<?php
// Starting the Hydroconsult tools.
// Tools are started WITHOUT cmd.exe: proc_open() gets an array of arguments, so
// characters like & | > ^ % " in user input are passed as plain text and are
// never interpreted by a shell. Every value is still validated before use.

if (!defined('TOOL_DIR')) {
    define('TOOL_DIR', 'c:/Program Files/Hydroconsult/');
}

function sanitize_input($input) {
    if (is_string($input)) {
        return escapeshellcmd($input);
    } elseif (is_array($input)) {
        return array_map('sanitize_input', $input);
    }

    return $input;

}

// Starts a tool in the background (like the former "start /B") and returns
// immediately. $exe is relative to TOOL_DIR. Returns false if an argument is
// invalid or the process could not be started.
function start_tool(string $exe, array $args): bool {
    $path = TOOL_DIR . $exe;
    if (!is_file($path)) {
        error_log('start_tool: onbekend programma ' . $exe);
        return false;
    }
    $argv = [$path];
    foreach ($args as $arg) {
        if (!is_scalar($arg)) {
            return false;
        }
        $arg = (string)$arg;
        // control characters (except newline/tab in free text) and overlong values are refused
        if (strlen($arg) > 4000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $arg)) {
            error_log('start_tool: ongeldig argument voor ' . $exe);
            return false;
        }
        $argv[] = $arg;
    }
    $nul = [0 => ['file', 'NUL', 'r'], 1 => ['file', 'NUL', 'w'], 2 => ['file', 'NUL', 'w']];
    $proc = proc_open($argv, $nul, $pipes, dirname($path), null, ['bypass_shell' => true]);
    // No proc_close(): that would wait for the tool to finish.
    return is_resource($proc);
}

// ---- validation -----------------------------------------------------------

// Characters with a special meaning for cmd.exe. Never accepted in values that
// go to a tool, even though tools are started without a shell.
const SHELL_CHARS = '"&|<>^%`';

function has_shell_chars(string $value): bool {
    return strpbrk($value, SHELL_CHARS) !== false;
}

function valid_mail($mail): ?string {
    if (!is_string($mail)) {
        return null;
    }
    $mail = trim($mail);
    if (strlen($mail) > 254 || has_shell_chars($mail) || filter_var($mail, FILTER_VALIDATE_EMAIL) === false) {
        return null;
    }
    return $mail;
}

// Free text such as a name. Newlines only when $multiline. Shell characters are
// refused unless $allowShellChars (only for the feedback message, which is plain text for the mail).
function valid_text($text, int $maxLength, bool $multiline = false, bool $allowShellChars = false): ?string {
    if (!is_string($text)) {
        return null;
    }
    $text = trim($text);
    // preg with /u: false for invalid UTF-8, otherwise the number of characters (mbstring is not installed)
    $length = preg_match_all('/./su', $text);
    if ($text === '' || $length === false || $length > $maxLength) {
        return null;
    }
    $forbidden = $multiline ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/' : '/[\x00-\x1F\x7F]/';
    if (preg_match($forbidden, $text) || (!$allowShellChars && has_shell_chars($text))) {
        return null;
    }
    return $text;
}

// Short technical values: dates, numbers, session ids, station codes, TRUE/FALSE.
function valid_token($value, int $maxLength = 40): ?string {
    if (!is_scalar($value)) {
        return null;
    }
    $value = (string)$value;
    if (!preg_match('/^[A-Za-z0-9_.,\/:-]{1,' . $maxLength . '}$/', $value) || strpos($value, '..') !== false) {
        return null;
    }
    return $value;
}

// Name of an uploaded file (no path): letters (also accented), digits, spaces and . , _ - ( ) +
function valid_filename($name, string $extension): ?string {
    if (!is_string($name)) {
        return null;
    }
    $name = trim($name);
    if (!preg_match('/^[\p{L}\p{N} ._,()+-]{1,120}$/u', $name) || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== $extension
        || $name[0] === '.') {
        return null;
    }
    return $name;
}

// Name and mail address of the logged-in user, taken from the 'gebruiker' cookie.
// The cookie is user-controlled, so both are validated. Returns null if invalid.
function valid_user_cookie(): ?array {
    $gebruiker = $_COOKIE['gebruiker'] ?? null;
    if (!is_array($gebruiker)) {
        return null;
    }
    $naam = valid_text($gebruiker['naam'] ?? null, 100);
    $mail = valid_mail($gebruiker['mail'] ?? null);
    $sessionid = valid_token($gebruiker['sessionid'] ?? null, 20);
    if ($naam === null || $mail === null || $sessionid === null) {
        return null;
    }
    return ['naam' => $naam, 'mail' => $mail, 'sessionid' => $sessionid];
}

// ---- numbering ---------------------------------------------------------------
// Session ids and order numbers come from sequences (not from counting rows: a deleted row
// would then hand out an existing number again). Returns null if the database refuses.

const SEQ_SESSIONID = 'public.tbaanvrager_sessionid_seq';
const SEQ_BESTELLINGNR = 'public.tbbasis_bestellingnr_seq';

function next_id($dbHandle, string $sequence): ?int {
    $result = pg_query_params($dbHandle, 'SELECT nextval($1::regclass)', [$sequence]);
    if ($result === false) {
        error_log('Meteobase: nextval mislukt voor ' . $sequence . ': ' . pg_last_error($dbHandle));
        return null;
    }
    return (int)pg_fetch_result($result, 0, 0);
}

// ---- messages for orders ---------------------------------------------------
// Shown by index.php as a red alert (inside a single-quoted JS string: no apostrophes).

const MSG_INVALID_USER = 'Uw bestelling is niet verwerkt: uw naam of e-mailadres is ongeldig of bevat niet toegestane tekens (aanhalingsteken, &, |, <, >, ^, %, `). Registreer opnieuw via Aanmelden.';
const MSG_INVALID_INPUT = 'Uw bestelling is niet verwerkt: de opgegeven selectie is ongeldig. Controleer uw invoer en probeer het opnieuw.';
const MSG_TOOL_FAILED = 'Uw bestelling kon niet worden gestart door een technische fout. Probeer het later opnieuw of neem contact op via info@meteobase.nl.';

function order_error(string $message, string $detail = ''): void {
    $_SESSION['errorMsg'] = $message;
    unset($_SESSION['dataFeedbackMsg']);
    error_log('Meteobase bestelling geweigerd: ' . ($detail !== '' ? $detail : $message));
}
