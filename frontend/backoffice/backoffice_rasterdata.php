<html>
  <head>
    <title>Script Rasterdata </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This scripte writes the ordered raster-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'InlogNaam'] etc.
    // ** Database-connection is at ser level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** backoffice_rasterdata.php   v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2011   www.geopro.nl
    // *********************************************************
?>

  <?php
    // Validating input of the Table Name :
	$ValidatedGegevenstype = check_input($gegevenstype,"");
	$ValidatedWaarde = check_input($waarde,"");
	$ValidatedDatum_van = check_input($datum_van,"");
	$ValidatedDatum_tot = check_input($datum_tot,"");
	$ValidatedCoord_0 = check_input($coord_0,"");
	$ValidatedCoord_1 = check_input($coord_1,"");
	$ValidatedCoord_2 = check_input($coord_2,"");
	$ValidatedCoord_3 = check_input($coord_3,"");
	$ValidatedNeerslag = check_input($neerslag,"");
	$ValidatedMakkink = check_input($makkink,"");
	$ValidatedPenman = check_input($penman,"");
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
    $sTableName = "tbraster";

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
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
    	test_echo("<BR>"."Aantal records : ".$iNumberRows . "<BR>");
		test_echo("SESSIONID = " . $sessionid . "<BR>");


    // Compile the insertion-string, start with the Keys :
    	$sInsertQuery = "INSERT INTO $sTableName (" ;
    	$sInsertQuery .= "bestellingnr" . ",";
    	$sInsertQuery .= " minphi" . ",";
    	$sInsertQuery .= " minlab" . ",";
		$sInsertQuery .= " maxphi" . ",";
    	$sInsertQuery .= " maxlab" . ",";
    	$sInsertQuery .= " formaat" . ",";
    	$sInsertQuery .= " sessienr" . ",";
    	$sInsertQuery .= " neerslag" . ",";
		$sInsertQuery .= " penman" . ",";
		$sInsertQuery .= " makkink" . ",";
		$sInsertQuery .= " startdatum" . ",";
		$sInsertQuery .= " stopdatum)";

    // and add the Values :
    	$sInsertQuery .= " VALUES (";
    	$sInsertQuery .= ($iNumberRows +1). ",";
    	$sInsertQuery .= "'" . $ValidatedCoord_0 . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedCoord_1 . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedCoord_2 . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedCoord_3 . "'". ",";
    	$sInsertQuery .= "'" . "_UNSET". "'". ",";
    	$sInsertQuery .= "'" . $sessionid . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedNeerslag . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedPenman . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedMakkink . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedDatum_van . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedDatum_tot . "')";

		test_echo("<BR>" . $sInsertQuery . "<BR>");

    //  ** The actual insertion into the database :
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