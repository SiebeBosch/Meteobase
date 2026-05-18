<div id="gegevenspaneel_aanmelden">
<?php
// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
// Version 6-7
// Meteobase

include('local_config.php');
require 'sanitize.php';

$bericht = '';
$data = '';
$waarde_check = '';

function check_input($data,$problem='')
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  $data = pg_escape_string($data);
  if ($problem && strlen($data) == 0)
  {
    echo "Fout in Input "."<BR>";
    die($problem);
  }
  return $data ;
}

if(isset($_POST['subrd'])){
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		http_response_code(403);
		exit('Security validation failed');
	}
	
	// Honeypot check
	if (!empty($_POST['website_url_hp'])) {
		exit(); 
	}
	
	// Time-based check
	$min_submission_time = 3;
	$form_start_time = isset($_POST['form_timestamp']) ? (int)$_POST['form_timestamp'] : 0;
	if ($form_start_time === 0 || (time() - $form_start_time) < $min_submission_time) {
		exit(); // Silent fail - too fast
	}
	
	// Rate limiting - max 5 downloads per 10 minutes
	$max_downloads = 5;
	$time_window = 600; // 10 minutes
	
	if (!isset($_SESSION['raster_download_attempts'])) {
		$_SESSION['raster_download_attempts'] = [];
	}
	
	// Clean old attempts
	$_SESSION['raster_download_attempts'] = array_filter(
		$_SESSION['raster_download_attempts'],
		function($timestamp) use ($time_window) {
			return (time() - $timestamp) < $time_window;
		}
	);
	
	// Check if limit exceeded
	if (count($_SESSION['raster_download_attempts']) >= $max_downloads) {
		http_response_code(429);
		exit('Te veel download aanvragen. Probeer het over 10 minuten opnieuw.');
	}
	
	// Record this attempt
	$_SESSION['raster_download_attempts'][] = time();
	
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
//            $locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\\' . $file_name;
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
    if(isset($_POST['evtact'])){
        $evtact = $_POST['evtact'];
        $gegevenstype.= $evtact . ' ';
        $EVTACT = "true";
    } else {
        $EVTACT = "false";
    }
    if(isset($_POST['evtsho'])){
        $evtsho = $_POST['evtsho'];
        $gegevenstype.= $evtsho . ' ';
        $EVTSHO = "true";
    } else {
        $EVTSHO = "false";
    }
    $formaat = $_POST['fileType'];
	$veldnaam = "";
    if ($formaat == 'csv' || $formaat == 'sobek') {
		$locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\\' . $_COOKIE['ZIPFILE'];
        $veldnaam = addslashes($_POST['veldnaam']);
    }
	if(isset($_POST['sommen']))
		$sommen = $_POST['sommen'];
	
	//echo("EVTACT is " . $EVTACT);
	//echo("EVTSHO is " . $EVTSHO);
    
    //$waarde = $_POST['waarde'];
    $datum_van = $_POST['fromDate'];
    $datum_tot = $_POST['toDate'];
    $gebruiker = $_COOKIE['gebruiker'];
    $mail = $gebruiker['mail'];
    $naam = $gebruiker['naam'];
    $sessionid = $gebruiker['sessionid'];
    check_input($datum_van);
    check_input($datum_tot);
//    $tel = $gebruiker['tel'];

//Gekopieerd van backoffice_basisgegevens
// ****** Login-data *********
    $sConnectionString="UNSET";
    $dbHandle=0;
    $sMasterUser = MASTERUSER;
    $sMasterPassword = MASTERPASSWORD;
    $sHost = HOST;
    $sPoort = POORT;
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
        pg_free_result($dbHandle);
        pg_close($dbHandle);
        exit;
    }
// Access the table and determine the name of the export-file:
    $sQuery = "SELECT * FROM $sTableName";
    pg_prepare($dbHandle, "rasterGetQuery", $sQuery);
    $sResult = pg_execute($dbHandle, "rasterGetQuery", array());
    $iNumberRows = pg_num_rows($sResult);
    $NewOrder = $iNumberRows + 1;
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
	$XMIN = 0;
	if(isset($_COOKIE['minX'])){    
		$XMIN = round($_COOKIE['minX'], 0);
	}
	$YMIN = 0;
	if(isset($_COOKIE['minY'])){
		$YMIN = round($_COOKIE['minY'], 0);
	}
	$XMAX = 0;
	if(isset($_COOKIE['minY'])){
    $XMAX = round($_COOKIE['maxX'], 0);
	}
	$YMAX = 0;
	if(isset($_COOKIE['maxX'])){
    $YMAX = round($_COOKIE['maxY'], 0);
	}
    
    
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

    pg_prepare(
        $dbHandle,
        "rasterDataInsert",
        $sInsertQuery,
    );

    //echo($sInsertQuery . "<BR>");
    //echo("----------------------------<BR>");
    $bResult = pg_execute(
        $dbHandle,
        "rasterDataInsert",
        array(
            $sessionid,
            $FDATE,
            $TDATE,
            $iNumberRows +1,
            "$NSL GRID",
            "$PENMAN GRID",
            "$MAKKINK GRID",
        ),
    );

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
    
    $cmd_array = array(
    'ascii' => '"c:/Program Files/Hydroconsult/RASTER2ASCII/WIWBRASTER2ASCII.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' . $EVTSHO . ' ' . strtoupper($formaat) . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" ' . $mail,
    'hdf5' => '"c:/Program Files/Hydroconsult/NATIVERASTER/WIWBNATIVERASTER.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' . $EVTSHO . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" ' . $mail,
    'modflow' => '"c:/Program Files/Hydroconsult/RASTER2ASCII/WIWBRASTER2ASCII.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' . $EVTSHO . ' ' . strtoupper($formaat) . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" ' . $mail,
    'simgro' => '"c:/Program Files/Hydroconsult/RASTER2ASCII/WIWBRASTER2ASCII.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' . $EVTSHO . ' ' . strtoupper($formaat) . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" "' . $mail,
    'sobek' => '"c:/Program Files/Hydroconsult/RASTERBYPOLY/WIWBRASTERBYPOLY.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' . $EVTSHO . ' SOBEK ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" "' . $mail . '" "' . $locatie_zip . '" "' . $veldnaam . '"',
    'csv' => '"c:/Program Files/Hydroconsult/RASTERBYPOLY/WIWBRASTERBYPOLY.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' ' . $EVTACT . ' ' .$EVTSHO . ' CSV ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" "' . $mail . '" "' . $locatie_zip . '" "' . $veldnaam . '"'
    );
    
    $cmd = $cmd_array[$formaat];
    $cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
    pclose(sanitezedPopen($cmd, 'r'));
    
    //include('backoffice/backoffice_rasterdata.php');
    
    $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
    //$bericht = $bericht . '<br><br>' . 'Geachte ' . $naam . ',</p><p>Als de bevraging succesvol was, kunnen de opgevraagde gegevens met sessie-ID ' . $sessionid . ' worden gedownload van de ' . $link . '.<br>';
    $bericht = 'Geachte ' . $naam . ',</p><p>Uw bestelling wordt verwerkt. U ontvangt nu een e-mail met een downloadlink.<br />';
    $data = 'Gegevenstype: ' . $gegevenstype . '<br>Datum: ' . $datum_van . ' tot ' . $datum_tot . '<br>RD-Co&ouml;rdinaten (zuidwest): ' . round($coord_0, 0) . ', ' . round($coord_1, 0) . '<br />RD-Co&ouml;rdinaten (noordoost): ' . round($coord_2, 0) . ', ' . round($coord_3, 0) . '</p>';
    
    // Regenerate CSRF token after successful submission to prevent resubmission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $_SESSION['dataFeedbackMsg'] = "send message";
    
    include('pages/raster01.php');
    
}else{
    include('pages/raster01.php');
}
?>
