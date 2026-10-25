<?php
// Meteobase
// Version 6-7
// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
// ** Simplified by Siebe Bosch on 2020-01-13
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
	
	if (!isset($_SESSION['toets_download_attempts'])) {
		$_SESSION['toets_download_attempts'] = [];
	}
	
	// Clean old attempts
	$_SESSION['toets_download_attempts'] = array_filter(
		$_SESSION['toets_download_attempts'],
		function($timestamp) use ($time_window) {
			return (time() - $timestamp) < $time_window;
		}
	);
	
	// Check if limit exceeded
	if (count($_SESSION['toets_download_attempts']) >= $max_downloads) {
		http_response_code(429);
		exit('Te veel download aanvragen. Probeer het over 5 minuten opnieuw.');
	}
	
	// Record this attempt
	$_SESSION['toets_download_attempts'][] = time();
	
    if (!isset($_COOKIE['cookiebar']) || $_COOKIE['cookiebar'] == "CookieDisallowed") {
        order_error('Uw bestelling is niet verwerkt: accepteer eerst de cookies, die zijn nodig om uw gegevens te onthouden.', 'toetsingsdata: cookies geweigerd');
        include('toets01.php');
        return;
    }

	// Validate before the order is stored; the values go to a tool (see sanitize.php)
	$user = valid_user_cookie();
	if ($user === null) {
		order_error(MSG_INVALID_USER, 'toetsingsdata: gebruiker-cookie');
		include('toets01.php');
		return;
	}
	if (!in_array($_POST['dataType'], ['stochasten_2015', 'stochasten_2019', 'stochasten_2024',
			'tijdreeksen_2015', 'tijdreeksen_2019', 'tijdreeksen_2024'], true)) {
		order_error(MSG_INVALID_INPUT, 'toetsingsdata: dataType');
		include('toets01.php');
		return;
	}
	$gestart = false;

	if ($_POST['dataType'] == 'stochasten_2024') {
		
		// Verwerken van het formulier
        // validated at the start of the order
        $mail = $user['mail'];
        $naam = $user['naam'];
        $sessionid = $user['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		$gestart = exportStochasten('FALSE', 'FALSE', 'TRUE', $sessionid, $NewOrder, $naam, $mail);
	} elseif ($_POST['dataType'] == 'stochasten_2019') {
		
		// Verwerken van het formulier
        // validated at the start of the order
        $mail = $user['mail'];
        $naam = $user['naam'];
        $sessionid = $user['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		$gestart = exportStochasten('FALSE', 'TRUE', 'FALSE', $sessionid, $NewOrder, $naam, $mail);
		
	} elseif ($_POST['dataType'] == 'stochasten_2015') {
		
		// Verwerken van het formulier
        // validated at the start of the order
        $mail = $user['mail'];
        $naam = $user['naam'];
        $sessionid = $user['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		$gestart = exportStochasten('TRUE', 'FALSE', 'FALSE', $sessionid, $NewOrder, $naam, $mail);
    
    }else {
        // validated at the start of the order
        $mail = $user['mail'];
        $naam = $user['naam'];
        $sessionid = $user['sessionid'];
		
		$STATS2015='FALSE';
		$STATS2019='FALSE';
		$STATS2024='FALSE';
		if ($_POST['dataType'] == 'tijdreeksen_2015') {
			$STATS2015='TRUE';
		} elseif ($_POST['dataType'] == 'tijdreeksen_2019') {
			$STATS2019='TRUE';
		} elseif ($_POST['dataType'] == 'tijdreeksen_2024') {
			$STATS2024='TRUE';
		}
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
        test_echo($bericht);
    
    
        //initialiseer de zichtjaren voor de bestellingendatabase
        $ZJ_HUIDIG='FALSE';
        $ZJ_2030='FALSE';
        $ZJ_2050='FALSE';
        $ZJ_2085='FALSE';
    
    
        if(isset($_POST['HUIDIG'])){
            $HUIDIG='TRUE';
            $ZJ_HUIDIG='TRUE';
        }
        else
        {
            $HUIDIG='FALSE';
        }
    
    
        if(isset($_POST['ALL_2030'])){
            $ALL_2030='TRUE';
            $ZJ_2030='TRUE';
        }
        else
        {
            $ALL_2030='FALSE';
        }
    
    
        if(isset($_POST['GL_2050'])){
            $GL_2050='TRUE';
            $ZJ_2050='TRUE';
        }
        else
        {
            $GL_2050='FALSE';
        }
    
    
        if(isset($_POST['GH_2050'])){
            $GH_2050='TRUE';
            $ZJ_2050='TRUE';
        }
        else
        {
            $GH_2050='FALSE';
        }
    
    
        if(isset($_POST['WL_2050'])){
            $WL_2050='TRUE';
            $ZJ_2050='TRUE';
        }
        else
        {
            $WL_2050='FALSE';
        }
    
    
        if(isset($_POST['WH_2050'])){
            $WH_2050='TRUE';
            $ZJ_2050='TRUE';
        }
        else
        {
            $WH_2050='FALSE';
        }
    
    
        if(isset($_POST['GL_2085'])){
            $GL_2085='TRUE';
            $ZJ_2085='TRUE';
        }
        else
        {
            $GL_2085='FALSE';
        }
    
    
        if(isset($_POST['GH_2085'])){
            $GH_2085='TRUE';
            $ZJ_2085='TRUE';
        }
        else
        {
            $GH_2085='FALSE';
        }
    
    
        if(isset($_POST['WL_2085'])){
            $WL_2085='TRUE';
            $ZJ_2085='TRUE';
        }
        else
        {
            $WL_2085='FALSE';
        }
    
    
        if(isset($_POST['WH_2085'])){
            $WH_2085='TRUE';
            $ZJ_2085='TRUE';
        }
        else
        {
            $WH_2085='FALSE';
        }
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'TRUE','FALSE', $ZJ_HUIDIG, $ZJ_2030, $ZJ_2050, $ZJ_2085);
        
	$gestart = exportTijdreeksen($STATS2015, $STATS2019, $STATS2024, $HUIDIG, $ALL_2030, $GL_2050, $GH_2050, $WL_2050, $WH_2050, $GL_2085, $GH_2085, $WL_2085, $WH_2085, $sessionid, $NewOrder, $naam, $mail);
    }

    if (!$gestart) {
        order_error(MSG_TOOL_FAILED, 'toetsingsdata: ' . $_POST['dataType'] . ' niet gestart');
        include('toets01.php');
        return;
    }

    // Regenerate CSRF token after successful submission to prevent resubmission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $_SESSION['dataFeedbackMsg'] = 'send message';
    include('toets01.php');
} else {
    include('toets01.php');
}

?>
		
<?php

	function exportTijdreeksen($STATS2015, $STATS2019, $STATS2024, $HUIDIG, $ALL_2030, $GL_2050, $GH_2050, $WL_2050, $WH_2050, $GL_2085, $GH_2085, $WL_2085, $WH_2085, $psessionid, $pNewOrder, $pname, $pmail)
	{
	
	$args = [$STATS2015, $STATS2019, $STATS2024, $HUIDIG, $ALL_2030, $GL_2050, $GH_2050, $WL_2050, $WH_2050, $GL_2085, $GH_2085, $WL_2085, $WH_2085, $psessionid, $pNewOrder];
	$pname = valid_text($pname, 100);
	$pmail = valid_mail($pmail);
	if ($pname === null || $pmail === null || in_array(null, array_map('valid_token', $args), true)) {
		return false;
	}
	return start_tool('WIWBTOETSING/WIWBTOETSING.exe', array_merge($args, [$pname, $pmail]));
	}
	
	function exportStochasten($STATS2015, $STATS2019, $STATS2024, $psessionid, $pNewOrder, $pname, $pmail)
	{
	
	$args = [$STATS2015, $STATS2019, $STATS2024, $psessionid, $pNewOrder];
	$pname = valid_text($pname, 100);
	$pmail = valid_mail($pmail);
	if ($pname === null || $pmail === null || in_array(null, array_map('valid_token', $args), true)) {
		return false;
	}
	return start_tool('WIWBSTOCHASTEN/WIWBSTOCHASTEN.exe', array_merge($args, [$pname, $pmail]));
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
        pg_prepare($dbHandle, "toetsingdataInsertQuery", $sInsertQuery);
        $bResult =  pg_execute($dbHandle, "toetsingdataInsertQuery", array(
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
