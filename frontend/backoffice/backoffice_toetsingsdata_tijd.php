<html>
  <head>
    <title>Toetsingsdata (Tijd) </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet writes the Toetsings-data (Tijd) into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'InlogNaam'] etc.
    // ** Database-connection is at end-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10    V-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** backoffice_toetsingsdata_tijd.php
    // ** Copyright GeoPro 2012   www.geopro.nl
	// ** Redesign by Siebe Bosch on 2015-10-03
    // *********************************************************
?>

  <?php
    // Validating input of the Table Name :
    	$ValidatedUser = check_input($gebruiker['naam'],"");
    	$ValidatedCompany = check_input($gebruiker['org'],"");
    	$ValidatedPhone = check_input($gebruiker['tel'],"");
    	$ValidatedEmail = check_input($gebruiker['mail'],"");
		$ValidatedKlimaat = check_input($ks,"");
		$ValidatedGegevenstype = check_input($gegevenstype,"");
		$ValidatedShapefile = check_input($sf,"");
		$ValidatedWaarde = check_input($waarde,"");
		$ValidatedDatum_van = check_input($datum_van,"");
		$ValidatedDatum_tot = check_input($datum_tot,"");

		test_echo($gegevenstype . "<BR>");
		test_echo($neerslag . "<BR>");
		test_echo($makkink . "<BR>");
		test_echo($penman . "<BR>");
  ?>

  <body>

<?php 

	include('..'.DIRECTORY_SEPARATOR.'local_config.php');

    // ****** Login-data *********
    $sConnectionString="UNSET";
    $dbHandle=0;
    $sMasterUser = MASTERUSER;
    $sMasterPassword = MASTERPASSWORD;
    $sHost = HOST;
    $sPoort =POORT;
    $sDataBase = DATABASE;
    $sTableName = "tbtoets";

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
		exit;
		}
	ELSE
		{
        	$version = pg_version($dbHandle);
			test_echo(".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
    		test_echo("Database-handle     = ".$dbHandle . "<BR>");
    		test_echo("PostgreSQL-client   = ".$version['client']. "<BR>");
    		test_echo("          -protocol = ".$version['protocol']. "<BR>");
    		test_echo("          -server   = ".$version['server']. "<BR>");
		}

    // Access the table :
    	$sQuery = "SELECT * FROM " .$sTableName ;    
    	test_echo("<BR>" . "Query : ". $sQuery . "<BR>");
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
    	test_echo("Aantal records : ".$iNumberRows . "<BR>");
    
    // Compile the insertion-string, start with the Keys :
    	$sInsertKeys = "INSERT INTO $sTableName (" ;
    	$sInsertKeys .= "sessienr" . ",";
    	$sInsertKeys .= " bestellingnr" . ",";
    	$sInsertKeys .= " typetoets" . ",";
		$sInsertKeys .= " klimaatscenario" . ",";
		$sInsertKeys .= " neerslag" . ",";
		$sInsertKeys .= " makkink" . ",";
		$sInsertKeys .= " penman" . ",";
		$sInsertKeys .= " shapefile" . ",";
		$sInsertKeys .= " tijdstap" . ",";
		$sInsertKeys .= " startdatum" . ",";
		$sInsertKeys .= " stopdatum";
    	$sInsertKeys .= ")";
		test_echo("KEYS   = " . $sInsertKeys);

    // Add the Values :
		$sInsertValues  = " VALUES (";
		$sInsertValues .= "'" . $sessionid . "'". ",";
		$sInsertValues .= ($iNumberRows +1) . ",";
		$sInsertValues .= "'" . "Tijdreeks" . "'". ",";
		$sInsertValues .= "'" . $ValidatedKlimaat . "'". ",";
		$sInsertValues .= "'" . $neerslag . "'". ",";
		$sInsertValues .= "'" . $makkink . "'". ",";
		$sInsertValues .= "'" . $penman . "'". ",";
		$sInsertValues .= "'" . $ValidatedShapefile . "'". ",";
		$sInsertValues .= "'" . $ValidatedWaarde . "'". ",";
		$sInsertValues .= "'" . $ValidatedDatum_van . "'". ",";
		$sInsertValues .= "'" . $ValidatedDatum_tot . "'";
		$sInsertValues .=  ")";

		test_echo("<BR>"."VALUES = " . $sInsertValues) ;

    // And combine :
    $sInsertQuery = $sInsertKeys . $sInsertValues ;
    test_echo("<BR>"."<BR>".$sInsertQuery . "<BR>");
    $bResult = pg_query($dbHandle, $sInsertQuery);
        if (!$bResult)
        {
          echo "<BR>"."De bestelling is niet opgeslagen in de database";
        }
        else
        {
          test_echo("<BR>"."Resulaat : ". $bResult . "<BR>");
        }

    // ** Clean-up :
    pg_free_result($result);
    pg_close($dbHandle);
    test_echo("<BR>");
	
	//** Time to write the results!    
	// ****** Login-data *********
    	$sConnectionString="UNSET";
    	$dbHandle=0;
    	$sMasterUser = "bas";
    	$sMasterPassword = "bas";
    	$sHost = "localhost";
    	$sPoort ="5432";
    	$sDataBase = "meteobase";
    	$sTableName = "tijdreeksen";

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
		exit;
		}
	ELSE
		{
        	$version = pg_version($dbHandle);
			test_echo(".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
    		test_echo("Database-handle     = ".$dbHandle . "<BR>");
    		test_echo("PostgreSQL-client   = ".$version['client']. "<BR>");
    		test_echo("          -protocol = ".$version['protocol']. "<BR>");
    		test_echo("          -server   = ".$version['server']. "<BR>");
		}
	
		// CONFIGURABLE >>>>>>>>>>
		$ExportPath = "C:/Program Files (x86)/PostgreSQL/EnterpriseDB-Apache/Php/apache/www/meteobase/downloads";
		$ExportFileNeerslag = "Bestelling_" . $sessionid . "_" . $NewOrder . "_Tijdreeks_ " . $ks . ".csv" ;
		$FileCsvNeerslag = $ExportPath . "/" . $ExportFileNeerslag ;
		// CONFIGURABLE >>>>>>>>>>

		ExportTijdreeks($dbHandle, $datum_van, $datum_tot, $area, $FileCsvNeerslag,$ks);
	

    // And echo the duration of the operation :
    $dStopTime = date_create();
    test_echo("Stop  Tijd : " . date_format($dStopTime , 'H:i:s') . "<BR>");
?>

  </body>
</html>

<?php
// This function will check all data entered into the form:
function check_input($data,$problem='')
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  if ($problem && strlen($data) == 0)
  {
    echo "Fout in Input "."<BR>";
    die($problem);
  }
  return $data ;
}

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

