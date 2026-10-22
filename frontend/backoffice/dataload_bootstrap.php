<?php
// Shared start of the DataLoad/DataValidation scripts.
// These scripts write to data.* and meta.* and may only be run from the command line
// on this server, e.g.:  C:\php8\php.exe C:\Apache24\htdocs\backoffice\DataLoad\ScriptProcessStations.php
// They use their own database user (meteobase_dataload), configured outside the web root.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Niet beschikbaar via de webserver.');
}
require_once 'C:/Apache24/conf/meteobase_dataload_config.php';
