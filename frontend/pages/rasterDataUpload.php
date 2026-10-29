<?php
// Upload of a zipped shapefile for rasterdata per polygon (Dropzone on the tab Rasterdata).
// index.php includes this file before any HTML output and exits afterwards, so the
// HTTP status and headers below reach the browser.
// Error: HTTP 400 (429 for too many uploads) with a short Dutch message as plain text.
// Success: HTTP 200 with JSON {bestand, aantal, velden} (field names from the .dbf).
// The zip is stored under a random name; that name, the original name and the field
// names are kept in $_SESSION['shape_uploads'], and the cookie ZIPFILE holds only the
// random name. pages/rasterdata.php accepts only an upload from this session.

require_once __DIR__ . '/../sanitize.php';
require_once __DIR__ . '/../shapefile_check.php';

const UPLOAD_MAX_PER_WINDOW = 10;
const UPLOAD_WINDOW = 600; // seconds
const UPLOAD_MSG_TOO_BIG = 'Het bestand is te groot (maximaal 20 MB).';
const UPLOAD_MSG_FAILED = 'Het uploaden is mislukt. Probeer het opnieuw.';

function upload_reply(int $status, string $body, string $type = 'text/plain'): void {
    http_response_code($status);
    header('Content-Type: ' . $type . '; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo $body;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    upload_reply(405, 'Een shapefile kan alleen via het formulier worden geüpload.');
}

// a request larger than post_max_size arrives without $_POST and $_FILES
if (!$_POST && !$_FILES && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    upload_reply(400, UPLOAD_MSG_TOO_BIG);
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !is_string($_POST['csrf_token'])
    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    upload_reply(400, 'Beveiligingsvalidatie mislukt. Laad de pagina opnieuw en probeer het nog eens.');
}

$user = valid_user_cookie();
if ($user === null) {
    upload_reply(400, 'U dient zich eerst te registreren.');
}

// Rate limiting - max 10 uploads per 10 minutes per session
$_SESSION['upload_attempts'] = array_values(array_filter(
    (array)($_SESSION['upload_attempts'] ?? []),
    function($timestamp) {
        return is_int($timestamp) && (time() - $timestamp) < UPLOAD_WINDOW;
    }
));
if (count($_SESSION['upload_attempts']) >= UPLOAD_MAX_PER_WINDOW) {
    upload_reply(429, 'Te veel uploads. Probeer het over 10 minuten opnieuw.');
}
$_SESSION['upload_attempts'][] = time();

$file = $_FILES['file'] ?? null;
if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
    upload_reply(400, 'Er is geen bestand ontvangen.');
}
switch ($file['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        upload_reply(400, UPLOAD_MSG_TOO_BIG);
    case UPLOAD_ERR_NO_FILE:
        upload_reply(400, 'Er is geen bestand ontvangen.');
    default:
        error_log('Meteobase upload: PHP-uploadfout ' . (int)$file['error']);
        upload_reply(400, UPLOAD_MSG_FAILED);
}
if (!is_uploaded_file($file['tmp_name'])) {
    upload_reply(400, UPLOAD_MSG_FAILED);
}

$origName = basename(str_replace('\\', '/', (string)$file['name']));
if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'zip') {
    upload_reply(400, 'Upload een zip-bestand met daarin de shapefile (.shp, .shx en .dbf).');
}

try {
    $info = shape_inspect_zip($file['tmp_name']);
} catch (ShapeCheckError $e) {
    upload_reply(400, $e->getMessage());
}

$stored = $user['sessionid'] . '_' . bin2hex(random_bytes(8)) . '.zip';
if (!move_uploaded_file($file['tmp_name'], SHAPE_UPLOAD_DIR . $stored)) {
    error_log('Meteobase upload: opslaan mislukt voor ' . $stored);
    upload_reply(500, 'Het bestand kon niet worden opgeslagen. Probeer het later opnieuw of neem contact op via info@meteobase.nl.');
}

// keep the last 10 uploads of this session
$uploads = (array)($_SESSION['shape_uploads'] ?? []);
$uploads[$stored] = ['naam' => $origName, 'velden' => $info['fields'], 'aantal' => $info['count'], 'tijd' => time()];
$_SESSION['shape_uploads'] = array_slice($uploads, -10, null, true);

setcookie('ZIPFILE', $stored, [
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

upload_reply(200, json_encode(
    ['bestand' => $origName, 'aantal' => $info['count'], 'velden' => $info['fields']],
    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
), 'application/json');
