<?php
// Meteobase
// Version 6-7
// ** Siebe Bosch on 2020-01-13
$bericht = '';
$data = '';
$waarde_check = '';

include('local_config.php');


// CONFIGURABLE >>>>>>>>>>
   $ExportPath = "C:\Apache24\htdocs\meteobase\downloads";
// CONFIGURABLE >>>>>>>>>>

$NewOrder = 0;
// Stochasten
if(isset($_POST['dataType'])){
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
    pclose(popen($cmd, 'r'));
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
    	$sResult = pg_query($dbHandle,$sQuery);
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
    	$sInsertQuery .= ($psessionid). ",";
    	$sInsertQuery .= ($NewOrder) . ",";
		$sInsertQuery .= ($tijdreeks) .",";
		$sInsertQuery .= ($stochast) .",";
    	$sInsertQuery .= ($ZJ_HUIDIG). ",";
    	$sInsertQuery .= ($ZJ_2030). ",";
    	$sInsertQuery .= ($ZJ_2050). ",";
    	$sInsertQuery .= ($ZJ_2085);
    	$sInsertQuery .= ")";

		
    	test_echo($sInsertQuery . "<BR>");
		test_echo("----------------------------<BR>");

		$bResult = pg_query($dbHandle, $sInsertQuery);
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
