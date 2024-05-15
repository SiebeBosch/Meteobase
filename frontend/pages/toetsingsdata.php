<?php
// Meteobase
// Version 6-7
// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
// ** Simplified by Siebe Bosch on 2020-01-13
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
	if ($_POST['dataType'] == 'stochasten_2024') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStochasten('FALSE', 'FALSE', 'TRUE', $sessionid, $NewOrder, $naam, $mail);
	} elseif ($_POST['dataType'] == 'stochasten_2019') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStochasten('FALSE', 'TRUE', 'FALSE', $sessionid, $NewOrder, $naam, $mail);
		
	} elseif ($_POST['dataType'] == 'stochasten_2015') {
		
		// Verwerken van het formulier
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
    
        $adres = 'https://www.meteobase.nl/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
        $bericht = 'De opgevraagde gegevens, met sessie-ID ' . $sessionid . ' kunnen over enkele minuten worden gedownload van de ' . $link . '.';
		
        $NewOrder = plaatsBestelling($sessionid, $NewOrder, 'FALSE','TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');
       
		//echo('Order number ' . $NewOrder);
		//echo('Session id ' . $sessionid);

		
		exportStochasten('TRUE', 'FALSE', 'FALSE', $sessionid, $NewOrder, $naam, $mail);
    
    }else {
        $gebruiker = $_COOKIE['gebruiker'];
        $mail = $gebruiker['mail'];
        $naam = $gebruiker['naam'];
        $sessionid = $gebruiker['sessionid'];
		
		$STATS2015='FALSE';
		$STATS2019='FALSE';
		if ($_POST['dataType'] == 'tijdreeksen_2015') {
			$STATS2015='TRUE';
		} elseif ($_POST['dataType'] == 'tijdreeksen_2019') {
			$STATS2019='TRUE';
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
        
	exportTijdreeksen($STATS2015, $STATS2019, $HUIDIG, $ALL_2030, $GL_2050, $GH_2050, $WL_2050, $WH_2050, $GL_2085, $GH_2085, $WL_2085, $WH_2085, $sessionid, $NewOrder, $naam, $mail);
    }
    $_SESSION['dataFeedbackMsg'] = 'send message';
    include('toets01.php');
} else {
    include('toets01.php');
}

?>
		
<?php

	function exportTijdreeksen($STATS2015, $STATS2019, $HUIDIG, $ALL_2030, $GL_2050, $GH_2050, $WL_2050, $WH_2050, $GL_2085, $GH_2085, $WL_2085, $WH_2085, $psessionid, $pNewOrder, $pname, $pmail)
	{
	
	$cmd = $STATS2015 . ' ' . $STATS2019 . ' ' . $HUIDIG . ' ' . $ALL_2030 . ' ' . $GL_2050 . ' ' . $GH_2050 . ' ' . $WL_2050 . ' ' . $WH_2050 . ' ' . $GL_2085 . ' ' . $GH_2085 . ' ' . $WL_2085 . ' ' . $WH_2085 . ' ' . $psessionid . ' ' . $pNewOrder . ' "' . $pname . '" "' . $pmail . '"';
	//echo($cmd);
	$cmd = '"c:/Program Files/Hydroconsult/WIWBTOETSING/WIWBTOETSING.exe" ' . $cmd ;
	//echo($cmd);
    $cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
    pclose(popen($cmd, 'r'));
	}
	
	function exportStochasten($STATS2015, $STATS2019, $STATS2024, $psessionid, $pNewOrder, $pname, $pmail)
	{
	
	$cmd = $STATS2015 . ' ' . $STATS2019 . ' ' . $STATS2024 . ' ' . $psessionid . ' ' . $pNewOrder . ' "' . $pname . '" "' . $pmail . '"';
	//echo($cmd);
	$cmd = '"c:/Program Files/Hydroconsult/WIWBSTOCHASTEN/WIWBSTOCHASTEN.exe" ' . $cmd;
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
