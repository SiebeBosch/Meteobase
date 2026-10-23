<?php
// Meteobase
// Version 6-7
// ** Siebe Bosch on 2020-01-13
$bericht = '';
$data = '';
$waarde_check = '';

include('local_config.php');
require_once 'sanitize.php';

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
	
	// Validate before the order is stored; the values go to WIWBSTEDELIJK (see sanitize.php)
	$jaren = ['stedelijk_2014' => 0, 'stedelijk_2030' => 1, 'stedelijk_2050' => 2, 'stedelijk_2085' => 3];
	$user = valid_user_cookie();
	if ($user === null) {
		order_error(MSG_INVALID_USER, 'stedelijk: gebruiker-cookie');
		include('sted01.php');
		return;
	}
	if (!isset($jaren[$_POST['dataType']])) {
		order_error(MSG_INVALID_INPUT, 'stedelijk: dataType');
		include('sted01.php');
		return;
	}
	$mail = $user['mail'];
	$naam = $user['naam'];
	$sessionid = $user['sessionid'];

	$adres = 'https://www.meteobase.nl/meteobase/downloads/';
	$link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
	$bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';

	// one of STATS2014/2030/2050/2085 (and the matching ZJ_ column) is TRUE
	$stats = ['FALSE', 'FALSE', 'FALSE', 'FALSE'];
	$stats[$jaren[$_POST['dataType']]] = 'TRUE';
	$NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE', 'TRUE', $stats[0], $stats[1], $stats[2], $stats[3]);
	if (!exportStedelijk($stats[0], $stats[1], $stats[2], $stats[3], $sessionid, $NewOrder, $naam, $mail)) {
		order_error(MSG_TOOL_FAILED, 'stedelijk: WIWBSTEDELIJK niet gestart');
		include('sted01.php');
		return;
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
	
	$args = [$STATS2014, $STATS2030, $STATS2050, $STATS2085, $psessionid, $pNewOrder];
	$pname = valid_text($pname, 100);
	$pmail = valid_mail($pmail);
	if ($pname === null || $pmail === null || in_array(null, array_map('valid_token', $args), true)) {
		return false;
	}
	return start_tool('WIWBSTEDELIJK/WIWBSTEDELIJK.exe', array_merge($args, [$pname, $pmail]));
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
    		// (PHP 8: geen pg_free_result op een verbinding)
    		pg_close($dbHandle);
		}
		ELSE
		{
        	$version = pg_version($dbHandle);
			test_echo(".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
    		test_echo("Database-handle     = (verbinding)<BR>");
    		test_echo("PostgreSQL-client   = ".$version['client']. "<BR>");
    		test_echo("          -protocol = ".$version['protocol']. "<BR>");
    		test_echo("          -server   = ".$version['server']. "<BR><BR>");
			test_echo("--------------------------------------------"."<BR>"); 

		}

		
		// Access the table :
		test_echo("<b>VASTLEGGING BESTELLING : </b><br>" );
        // New order number from the sequence (see next_id in sanitize.php)
        $NewOrder = next_id($dbHandle, SEQ_BESTELLINGNR);
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
          	test_echo("Resulaat : OK<BR>");
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
