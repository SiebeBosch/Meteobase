<?php
// Meteobase
// Version 6-7
// ** Siebe Bosch on 2020-01-13
$bericht = '';
$data = '';
$waarde_check = '';

include('local_config.php');
require 'sanitize.php';

// CONFIGURABLE >>>>>>>>>>
   $ExportPath = "C:\Apache24\htdocs\meteobase\downloads";
// CONFIGURABLE >>>>>>>>>>

$NewOrder = 0;
// Stochasten
if(isset($_POST['dataType'])){
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		http_response_code(403);
		exit('Security validation failed');
	}
	
	// Honeypot check
	if (!empty($_POST['website_url_hp'])) {
		http_response_code(403);
		exit(); 
	}
	
	// Time-based check
	$min_submission_time = 3;
	$form_start_time = isset($_POST['form_timestamp']) ? (int)$_POST['form_timestamp'] : 0;
	if ($form_start_time === 0 || (time() - $form_start_time) < $min_submission_time) {
		exit(); // Silent fail - too fast
	}
	
	// Rate limiting - max 3 downloads per 5 minutes
	$max_downloads = 3;
	$time_window = 300; // 5 minutes
	
	if (!isset($_SESSION['stedelijk_download_attempts'])) {
		$_SESSION['stedelijk_download_attempts'] = [];
	}
	
	// Clean old attempts
	$_SESSION['stedelijk_download_attempts'] = array_filter(
		$_SESSION['stedelijk_download_attempts'],
		function($timestamp) use ($time_window) {
			return (time() - $timestamp) < $time_window;
		}
	);
	
	// Check if limit exceeded
	if (count($_SESSION['stedelijk_download_attempts']) >= $max_downloads) {
		http_response_code(429);
		exit('Te veel download aanvragen. Probeer het over 5 minuten opnieuw.');
	}
	
	// Record this attempt
	$_SESSION['stedelijk_download_attempts'][] = time();
	
	if ($_POST['dataType'] == 'stedelijk_2014') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'FALSE', 'FALSE', 'FALSE');
       
		//echo('in stedelijk_2014');
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStedelijk('TRUE', 'FALSE','FALSE','FALSE', $sessionid, $NewOrder, $naam, $mail);
	} elseif ($_POST['dataType'] == 'stedelijk_2030') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'FALSE', 'TRUE', 'FALSE', 'FALSE');
       
		//echo('in stedelijk_2030');
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStedelijk('FALSE', 'TRUE','FALSE','FALSE', $sessionid, $NewOrder, $naam, $mail);
	} elseif ($_POST['dataType'] == 'stedelijk_2050') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'FALSE', 'FALSE', 'TRUE', 'FALSE');
       
		//echo('in stedelijk_2050');
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStedelijk('FALSE', 'FALSE','TRUE','FALSE', $sessionid, $NewOrder, $naam, $mail);
	} elseif ($_POST['dataType'] == 'stedelijk_2085') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'FALSE', 'FALSE', 'FALSE', 'TRUE');
       
		//echo('in stedelijk_2085');
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStedelijk('FALSE', 'FALSE','FALSE','TRUE', $sessionid, $NewOrder, $naam, $mail);
    
    } else {

		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';

		//echo('bestelling ongeldig');
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);		
	}
    
    // Regenerate CSRF token after successful submission to prevent resubmission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $_SESSION['dataFeedbackMsg'] = 'send message';
    include('sted01.php');
} else {
    include('sted01.php');
}

?>
		
<?php

	
	function exportStedelijk($STATS2014, $STATS2030, $STATS2050, $STATS2085, $psessionid, $pNewOrder, $pname, $pmail)
	{
	
	$cmd = $STATS2014 . ' ' . $STATS2030 . ' ' . $STATS2050 . ' ' . $STATS2085 . ' ' . $psessionid . ' ' . $pNewOrder . ' "' . $pname . '" "' . $pmail . '"';
	//echo($cmd);
	$cmd = '"c:/Program Files/Hydroconsult/WIWBSTEDELIJK/WIWBSTEDELIJK.exe" ' . $cmd;
	//echo($cmd);
    $cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
    pclose(sanitezedPopen($cmd, 'r'));
	}
	
	function plaatsBestelling($psessionid, $NewOrder, $tijdreeks, $stochast, $ZJ_HUIDIG, $ZJ_2030, $ZJ_2050, $ZJ_2085)
	{
	
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
		test_echo("***************** Parameters : *************"."<BR>"); 
		test_echo($sConnectionString."<BR>");
    // ** and connect to the database Server:
    	$dbHandle= pg_connect($sConnectionString);
    // ** Test for access to the database :
    	IF (!pg_ping($dbHandle) )
		{
		echo "Geen PostgreSQL server verbinding" . "<BR>";
    		pg_free_result($dbHandle);
    		pg_close($dbHandle);
		}
		ELSE
		{
        	$version = pg_version($dbHandle);
			test_echo(".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
    		test_echo("Database-handle     = ".$dbHandle . "<BR>");
    		test_echo("PostgreSQL-client   = ".$version['client']. "<BR>");
    		test_echo("          -protocol = ".$version['protocol']. "<BR>");
    		test_echo("          -server   = ".$version['server']. "<BR><BR>");
			test_echo("--------------------------------------------"."<BR>"); 

		}

		
		// Access the table :
		test_echo("<b>VASTLEGGING BESTELLING : </b><br>" );
    	$sQuery = "SELECT * FROM " .$sTableName ;    
    	test_echo("Query : ". $sQuery . "<BR>");
		pg_prepare($dbHandle, "stedelijkGet", $sQuery);
		$sResult =  pg_execute($dbHandle, "stedelijkGet", array()); 
    	$iNumberRows = pg_num_rows($sResult);
        $NewOrder = $iNumberRows + 1;
    	test_echo("Aantal records : ".$iNumberRows . "<BR>");
    	test_echo("Actueel Ordernummer : <b> ".$NewOrder . "</b><BR>");
		test_echo("----------------------------<BR>");

		// Compile the insertion-string, start with the Keys :
    	$sInsertQuery = "INSERT INTO $sTableName (" ;
    	$sInsertQuery .= "sessienr" . ",";
    	$sInsertQuery .= " bestellingnr" . ",";
		$sInsertQuery .= " tijdreeks" . ",";
		$sInsertQuery .= " stochast" . ",";
    	$sInsertQuery .= " \"ZJ_HUIDIG\"" . "," ;
    	$sInsertQuery .= " \"ZJ_2030\"" . "," ;
    	$sInsertQuery .= " \"ZJ_2050\"" . "," ;
    	$sInsertQuery .= " \"ZJ_2085\"";

		// and add the Values :
    	$sInsertQuery .= ") VALUES (";
    	$sInsertQuery .= "$1". ",";
    	$sInsertQuery .= "$2" . ",";
		$sInsertQuery .= "$3" .",";
		$sInsertQuery .= "$4" .",";
    	$sInsertQuery .= "$5". ",";
    	$sInsertQuery .= "$6". ",";
    	$sInsertQuery .= "$7". ",";
    	$sInsertQuery .= "$8";
    	$sInsertQuery .= ")";

		
    	test_echo($sInsertQuery . "<BR>");
		test_echo("----------------------------<BR>");
		pg_prepare($dbHandle, "stedelijkeInsertQuery", $sInsertQuery);
		$bResult =  pg_execute($dbHandle, "stedelijkeInsertQuery", array(
			$psessionid,
			$NewOrder,
			$tijdreeks,
			$stochast,
			$ZJ_HUIDIG,
			$ZJ_2030,
			$ZJ_2050,
			$ZJ_2085
		));
		test_echo(pg_last_error($dbHandle));
        if (!$bResult)
        	{
			echo $sInsertQuery;
          	echo "<BR>"."Bestelling kon niet in de database worden opgeslagen";
        	}
        else
        	{
          	test_echo("Resulaat : ". $bResult . "<BR>");
        	}

		// ** Clean-up :
    	pg_free_result($bResult);
    	pg_close($dbHandle);
		test_echo("--------------------------------------------"."<BR>");


	return $NewOrder;
	}
	

//	echo '<p>' . $bericht . '<br>'; 
//	echo $data . '</p>';

	//This function suppreses echo-statements in live-mode.
	//    It will have to be set for each separate module.
	function test_echo($EchoString)
{
	$Mode = 0; // Mode can be 0 = NoEcho, or 1 = Echo
	if ($Mode == 1)
	{
		echo($EchoString);
	}
}


	?>
