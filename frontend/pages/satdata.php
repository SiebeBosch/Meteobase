<div id="gegevenspaneel_aanmelden">
<?php
// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
// Version 6-7
// Meteobase

include('local_config.php');
require_once 'sanitize.php';

$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subrd'])){
    // Upload verwerken (Rasterdata, SOBEK)
    $locatie_zip = '';
    $valid_file = true;
//    if($_FILES['zipfile']['name']) {
//        if(!$_FILES['zipfile']['error']) {
//            $file_tmp = $_FILES['zipfile']['tmp_name'];
//            $file_name = strtolower($_FILES['zipfile']['name']);
//            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/meteobase2/uploads/";
//            if($_FILES['zipfile']['size'] > (6000000)) { $valid_file = false; }
//            $file_ext = end(explode('.',$file_name));
//            if($file_ext !== 'zip') { $valid_file = false; }
//            if($valid_file) { move_uploaded_file($file_tmp,"$upload_dir".strtolower($_FILES['zipfile']['name'])); }
//            $locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\' . $file_name;
//            $veldnaam = addslashes($_POST['veldnaam']);
//        }
//    }
    
    // Verwerken van het formulier
    $gegevenstype = '';
    if(isset($_POST['neerslag'])){
        $neerslag = $_POST['neerslag'];
        $gegevenstype.= $neerslag . ' ';
        $NSL = "true";
    } else {
        $NSL = "false";
        }
    if(isset($_POST['makkink'])){
        $makkink = $_POST['makkink'];
        $gegevenstype.= $makkink . ' ';
        $MAKKINK = "true";
    } else {
        $MAKKINK = "false";
    }
    if(isset($_POST['penman'])){
        $penman = $_POST['penman'];
        $gegevenstype.= $penman . ' ';
        $PENMAN = "true";
    } else {
        $PENMAN = "false";
    }
    $formaat = $_POST['fileType'];
    $zipNaam = null;
    if ($formaat == 'csv' || $formaat == 'sobek') {
        // same check as pages/rasterdata.php, before an order number is taken: the ZIPFILE cookie holds the
        // stored name of an upload of this session and this user (pages/rasterDataUpload.php, shapefile_check.php)
        require_once 'shapefile_check.php';
        $shapeUser = valid_user_cookie();
        if ($shapeUser === null) {
            order_error(MSG_INVALID_USER, 'satdata: gebruiker-cookie');
            include('pages/satdata01.php');
            return;
        }
        $zipNaam = valid_filename(basename(str_replace('\\', '/', (string)($_COOKIE['ZIPFILE'] ?? ''))), 'zip');
        $upload = $zipNaam !== null ? ($_SESSION['shape_uploads'][$zipNaam] ?? null) : null;
        if ($upload === null || strpos($zipNaam, $shapeUser['sessionid'] . '_') !== 0 || !is_file(SHAPE_UPLOAD_DIR . $zipNaam)) {
            order_error('Uw bestelling is niet verwerkt: er is geen geldige shapefile geüpload in deze sessie. Upload uw shapefile (zip) opnieuw.',
                'satdata: shapefile niet van deze sessie');
            include('pages/satdata01.php');
            return;
        }
        // the field must exist in the .dbf; the tool gets the name as it is written there
        $opgegeven = valid_text($_POST['veldnaam'] ?? null, 64);
        $veldnaam = null;
        foreach ($upload['velden'] as $veld) {
            if ($opgegeven !== null && strcasecmp($veld, $opgegeven) === 0) {
                $veldnaam = $veld;
            }
        }
        if ($veldnaam === null) {
            order_error('Uw bestelling is niet verwerkt: kies het veld met het gebieds-ID. Beschikbare velden: '
                . implode(', ', $upload['velden']) . '.', 'satdata: veldnaam niet in .dbf');
            include('pages/satdata01.php');
            return;
        }
        if (!shape_field_values_ok(SHAPE_UPLOAD_DIR . $zipNaam, $veldnaam)) {
            order_error('Het veld ' . $veldnaam . ' bevat lege of dubbele waarden. Ieder polygoon moet in dit veld een eigen, ingevulde waarde hebben.',
                'satdata: lege/dubbele waarden in ' . $veldnaam);
            include('pages/satdata01.php');
            return;
        }
    }
    $sommen = $_POST['sommen'];
    
    //$waarde = $_POST['waarde'];
    $datum_van = $_POST['fromDate'];
    $datum_tot = $_POST['toDate'];
    $gebruiker = $_COOKIE['gebruiker'];
    $mail = $gebruiker['mail'];
    $naam = $gebruiker['naam'];
    $sessionid = $gebruiker['sessionid'];
//    $tel = $gebruiker['tel'];

//Gekopieerd van backoffice_basisgegevens
// ****** Login-data *********
    $sConnectionString="UNSET";
    $dbHandle=0;
    $sMasterUser = MASTERUSER;
    $sMasterPassword = MASTERPASSWORD;
    $sHost = HOST;
    $sPoort =POORT;
    $sDataBase = DATABASE;
    $sTableName = "tbbasis";

// ** Compile the connection-string to the DB:
    $sConnectionString= "host=" .$sHost. " port=" .$sPoort. " user=" .$sMasterUser . " password=" .$sMasterPassword . " dbname=" .$sDataBase ;
// ** and connect to the database Server:
    $dbHandle= pg_connect($sConnectionString);
// ** Test for access to the database :
    IF (!pg_ping($dbHandle) )
    {
        echo "Geen PostgreSQL server verbinding" . "<BR>";
        // (PHP 8: geen pg_free_result op een verbinding)
        pg_close($dbHandle);
        exit;
    }
// New order number from the sequence (see next_id in sanitize.php)
    $NewOrder = next_id($dbHandle, SEQ_BESTELLINGNR);
    //echo("SessionID : ".$sessionid . "<br>");
    //echo("OrderID : ".$NewOrder . "<br>");
    //Gekopieerd van backoffice_basisgegevens

    // variabelen voor de EXE
    $datum_array = array();
    $FDATE = explode("/", $datum_van);
    $datum_array[] = $FDATE;
    $FDATE = $FDATE[2].$FDATE[1].$FDATE[0];
    $TDATE = explode("/", $datum_tot);
    $datum_array[] = $TDATE;
    $TDATE = $TDATE[2].$TDATE[1].$TDATE[0];
    $RasterDataDir = "d:\METEOBASE\RasterData";
    $XMIN = round($_COOKIE['minX'], 0);
    $YMIN = round($_COOKIE['minY'], 0);
    $XMAX = round($_COOKIE['maxX'], 0);
    $YMAX = round($_COOKIE['maxY'], 0);
    
//		echo('penmanbool:' . $PENMAN . '<br>');
//		echo('makkinkbool:' . $MAKKINK . '<br>');
//		echo('neerslagbool:' . $NSL . '<br>');
    
    $TempDir = '"c:\Result"';
    $GDALTOOLSDIR = '"D:\GDAL64"';

    $ExportFileName = '\Bestelling_' . $sessionid . '_' . $NewOrder . '_PENMAN.zip' ;
    //echo('Export File Name:' . $ExportFileName . '<br>');
    $ResultsZIPFile = chr(34) . 'c:\Apache24\htdocs\meteobase\downloads' . $ExportFileName . chr(34);
    //echo('ResultsZIPFile:' . $ResultsZIPFile . '<br>');
    
    // Compile the insertion-string, start with the Keys :
    $sInsertQuery = "INSERT INTO $sTableName (" ;
    $sInsertQuery .= "sessienr" . ",";
    $sInsertQuery .= " startdatum" . ",";
    $sInsertQuery .= " stopdatum" . ",";
    $sInsertQuery .= " bestellingnr" . ",";
    $sInsertQuery .= " neerslag" . ",";
    $sInsertQuery .= " penman" . ",";
    $sInsertQuery .= " makkink";

    // sla de bestelling op in de database
    $sInsertQuery .= ") VALUES (";
    $sInsertQuery .= "$1". ",";
    $sInsertQuery .= "$2". ",";
    $sInsertQuery .= "$3". ",";
    $sInsertQuery .= "$4". ",";
    $sInsertQuery .= "$5" . ",";
    $sInsertQuery .= "$6" . ",";
    $sInsertQuery .= "$7";
    $sInsertQuery .= ")";

    //echo($sInsertQuery . "<BR>");
    //echo("----------------------------<BR>");
    pg_prepare($dbHandle, "satDataInsert", $sInsertQuery);
    $bResult =  pg_execute($dbHandle, "satDataInsert", array(
        $sessionid,
        $FDATE,
        $TDATE,
        $NewOrder,
        "$NSL GRID",
        "$PENMAN GRID",
        "$MAKKINK GRID"
    ));
    if (!$bResult)
        {
        echo "<BR>"."Bestelling kon niet in de database worden opgeslagen";
        }
    else
        {
        //echo("Resultaat : ". $bResult . "<BR>");
        }
    
    // FOUTAFHANDELING //
    $ruimtelijke_selectie = true;
    $tijdselectie = true;
    // Ruimtelijke selectie //
    if(round(($XMIN)/1000)*1000 >= round(($XMAX)/1000)*1000 or round(($YMIN)/1000)*1000 >= round(($YMAX)/1000)*1000) {	$ruimtelijke_selectie = false; }
    if(round(($XMAX - $XMIN) / 1000) * round(($YMAX - $YMIN) / 1000) > 10000) { $ruimtelijke_selectie = false; }
    // Tijdselectie //
    if(intval(abs(strtotime($TDATE)-strtotime($FDATE))/86400) > 366*5) {
        $tijdselectie = false;
    }
    
    $adres = 'http://62.148.170.210/meteobase/downloads/';
    
    // Arguments for the tool; started without a shell (see sanitize.php)
    $user = valid_user_cookie();
    if ($user === null) {
        order_error(MSG_INVALID_USER, 'satdata: gebruiker-cookie');
        include('pages/satdata01.php');
        return;
    }
    $tokens = [$FDATE, $TDATE, $XMIN, $YMIN, $XMAX, $YMAX, $NSL, $MAKKINK, $PENMAN, $user['sessionid'], $NewOrder];
    if (in_array(null, array_map('valid_token', $tokens), true)) {
        order_error(MSG_INVALID_INPUT, 'satdata: argumenten');
        include('pages/satdata01.php');
        return;
    }
    $grid = [$FDATE, $TDATE, $XMIN, $YMIN, $XMAX, $YMAX, $NSL, $MAKKINK, $PENMAN];
    $poly = [$FDATE, $TDATE, $NSL, $MAKKINK, $PENMAN];
    $order = [$user['sessionid'], $NewOrder, $user['naam'], $user['mail']];
    if ($zipNaam !== null) {
        // checked above, before the order number was taken
        $order[] = 'c:\Apache24\htdocs\meteobase\uploads\\' . $zipNaam;
        $order[] = $veldnaam;
    }
    $tools = array(
    'ascii' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['ASCII', 'false'], $order)],
    'hdf5' => ['NATIVERASTER/WIWBNATIVERASTER.exe', array_merge($grid, ['false'], $order)],
    'modflow' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['MODFLOW', 'false'], $order)],
    'simgro' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['SIMGRO', 'false'], $order)],
    'sobek' => ['RASTERBYPOLY/WIWBRASTERBYPOLY.exe', array_merge($poly, ['SOBEK'], $order)],
    'csv' => ['RASTERBYPOLY/WIWBRASTERBYPOLY.exe', array_merge($poly, ['CSV'], $order)],
    );
    if (!isset($tools[$formaat])) {
        order_error(MSG_INVALID_INPUT, 'satdata: formaat');
        include('pages/satdata01.php');
        return;
    }
    if (!start_tool($tools[$formaat][0], $tools[$formaat][1])) {
        order_error(MSG_TOOL_FAILED, 'satdata: ' . $tools[$formaat][0] . ' niet gestart');
        include('pages/satdata01.php');
        return;
    }

    //include('backoffice/backoffice_rasterdata.php');

    $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
    //$bericht = $bericht . '<br><br>' . 'Geachte ' . $naam . ',</p><p>Als de bevraging succesvol was, kunnen de opgevraagde gegevens met sessie-ID ' . $sessionid . ' worden gedownload van de ' . $link . '.<br>';
    $bericht = 'Geachte ' . $naam . ',</p><p>Uw bestelling wordt verwerkt. U ontvangt nu een e-mail met een downloadlink.<br />';
    $data = 'Gegevenstype: ' . $gegevenstype . '<br>Datum: ' . $datum_van . ' tot ' . $datum_tot . '<br>RD-Co&ouml;rdinaten (zuidwest): ' . round($coord_0, 0) . ', ' . round($coord_1, 0) . '<br />RD-Co&ouml;rdinaten (noordoost): ' . round($coord_2, 0) . ', ' . round($coord_3, 0) . '</p>';
    
    $_SESSION['dataFeedbackMsg'] = "send message";
    
    include('pages/satdata01.php');
    
}else{
    include('pages/satdata01.php');
}
?>
