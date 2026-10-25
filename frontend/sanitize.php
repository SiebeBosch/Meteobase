<?php
function sanitize_input($input) {
    if (is_string($input)) {
        return escapeshellcmd($input); 
    } elseif (is_array($input)) {
        return array_map('sanitize_input', $input);
    }

    return $input;

}

function sanitezedPopen(string $command, string $mode) {    
    return popen(sanitize_input($command), $mode);
}
