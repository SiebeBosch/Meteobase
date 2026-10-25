<?php
// Not used by the site. Previously this file passed variables straight to exec();
// direct requests are now refused and the tool is started without a shell.
if (get_included_files()[0] === __FILE__) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../sanitize.php';
$args = [$FDATE ?? null, $TDATE ?? null, $XMIN ?? null, $YMIN ?? null, $XMAX ?? null, $YMAX ?? null,
    $NSL ?? null, $MAKKINK ?? null, $PENMAN ?? null, 'false', $sessionid ?? null, $NewOrder ?? null];
$naamOk = valid_text($naam ?? null, 100);
$mailOk = valid_mail($mail ?? null);
if ($naamOk !== null && $mailOk !== null && !in_array(null, array_map('valid_token', $args), true)) {
    start_tool('NATIVERASTER/WIWBNATIVERASTER.exe', array_merge($args, [$naamOk, $mailOk]));
}
?>