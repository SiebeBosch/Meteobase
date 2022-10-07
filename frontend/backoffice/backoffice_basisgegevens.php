<html>
  <head>
    <title>Creating Table </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet writes the login-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'InlogNaam'] etc.
    // ** Database-connection is at end-super-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-24
    // ** backoffice_basisgegevens.php   v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************

?>

  <?php
	die('here');
  
    // Validating input of the Table Name :
	$ValidatedGegevenstype = check_input($gegevenstype,"");
	$ValidatedWaarde = check_input($waarde,"");
	$ValidatedDatum_van = check_input($datum_van,"");
	$ValidatedDatum_tot = check_input($datum_tot,"");
	$ValidatedNeerslag = check_input($neerslag,"");
	$ValidatedMakkink = check_input($makkink,"");
//	$ValidatedPenman = check_input($penman,"");
	$ValidatedMeteostations = $meteostations;
  ?>

  <body>

<?php 

	include('..'.DIRECTORY_SEPARATOR.'local_config.php');

	// Determine which actions are to be taken :
	//   - Neerslag        (historical precipitation)
	//   - Penman          (historical evaporation)
	//   - Makkink         (to be implemented later)
	//   - Day- or hourly  (determines the dataset/stations to be exported)
	
	test_echo("<b>BESTELLING : </b><br>" );
	test_echo("neerslag : " . $neerslag . "<br>");
	test_echo("makkink : " . $makkink . "<br>");
//	test_echo("penman : " . $penman . "<br>");
	test_echo("tijdwaarde : " . $waarde . "<br>");
	test_echo("vanaf : " . $datum_van . "<br>");
	test_echo("tot : " . $datum_tot . "<br>");
	test_echo("naam : " . $naam . "<br>");
	test_echo("mail : " . $mail . "<br>");

	//echo 'test ' . $naam . ' ' . $mail;


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

	// Next, drop the view 
    	$sKillQuery = "DROP VIEW data.viewmakkink" ;    
    	test_echo("Query : ". $sKillQuery . "<BR>");
    	$sResult = pg_query($dbHandle,$sKillQuery);		
		
    // ** Assemble the String representing the Array with Stations :
		$strStations = "{";
		$strStationListNames = "";
		test_echo("<b>GESELECTEERDE STATIONS : </b><br>" );

		if(isset($meteostations))
		{
			foreach($meteostations as $key => $var)
			{
				test_echo("$key<br>");
				if ($var) {
				  $strStations .= '' . $key . ',';
				  $strStationListNames .= $key . ',';
				}
			}
			test_echo("___________Totaal : " . count($meteostations) . "<BR>");
			test_echo('<BR>');
		}
		
    // ** Remove the last "," and complete the Array :
		$strStationListNames = rtrim($strStationListNames,",");
		$strStations = rtrim($strStations,",");
		$strStations .=   "}";
		test_echo($strStations . "<br>");
		test_echo("........................."."<BR>");
		// And determine the station-numbers...
		$strStationNumbers = createListStationNumbers($meteostations,$dbHandle, $FieldsString, $NamesString, $StationsString, $waarde);
		test_echo($strStationNumbers . "<br>");
		test_echo("Fields = " . $FieldsString . "<br>");
		test_echo("Names = " . $NamesString . "<br>");
		test_echo("Stations = " . $StationsString . "<br>");
		test_echo("--------------------------------------------"."<BR>"); 
    // Access the table :
		test_echo("<b>VASTLEGGING BESTELLING : </b><br>" );
    	$sQuery = "SELECT * FROM " .$sTableName ;    
    	test_echo("Query : ". $sQuery . "<BR>");
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
    	test_echo("Aantal records : ".$iNumberRows . "<BR>");
		$NewOrder = $iNumberRows + 1;
    	test_echo("Actueel Ordernummer : <b> ".$NewOrder . "</b><BR>");		
		test_echo("----------------------------<BR>");
		if($ValidatedWaarde == 'Uurstations'){
			$ValidatedWaarde = 'Uur';
		} else {
			$ValidatedWaarde = 'Dag';
		}
    // Compile the insertion-string, start with the Keys :
    	$sInsertQuery = "INSERT INTO $sTableName (" ;
    	$sInsertQuery .= "sessienr" . ",";
    	$sInsertQuery .= " startdatum" . ",";
    	$sInsertQuery .= " stopdatum" . ",";
    	$sInsertQuery .= " bestellingnr" . ",";
    	$sInsertQuery .= " tijdswaarde" . ",";
    	$sInsertQuery .= " meteostations" . ",";
    	$sInsertQuery .= " neerslag" . ",";
		$sInsertQuery .= " penman" . ",";
		$sInsertQuery .= " makkink" ;

    // and add the Values :
    	$sInsertQuery .= ") VALUES (";
    	$sInsertQuery .= ($sessionid). ",";
		$sInsertQuery .= "'" . $ValidatedDatum_van . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedDatum_tot . "'". ",";
    	$sInsertQuery .= "'" . ($iNumberRows +1) . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedWaarde . "'". ",";
		$sInsertQuery .= "'" . $strStations  . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedNeerslag . "'". ",";
    	$sInsertQuery .= "'" . "FALSE". "',";
    	$sInsertQuery .= "'" . $ValidatedMakkink . "'";
    	$sInsertQuery .= ")";

    	test_echo($sInsertQuery . "<BR>");
		test_echo("----------------------------<BR>");

	$bResult = pg_query($dbHandle, $sInsertQuery);
		
        if (!$bResult)
        	{
          	echo "<BR>"."Bestelling kon niet in de database worden opgeslagen";
        	}
        else
        	{
          	test_echo("Resulaat : ". $bResult . "<BR>");
        	}

    // ** Clean-up :
    	pg_free_result($result);
    	pg_close($dbHandle);
		test_echo("--------------------------------------------"."<BR>");
		
    // ****** Login-data *********
		test_echo("<b>EXPORTFILES : </b><br>" );
    	$sConnectionString="UNSET";
    	$dbHandle=0;
    	$sMasterUser = "bas";
    	$sMasterPassword = "bas";
    	$sHost = "localhost";
    	$sPoort ="5432";
    	$sDataBase = "meteobase";
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
    	$sQuery = "SELECT * FROM " .$sTableName ;    
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
		test_echo("SessionID : ".$sessionid . "<br>");
		test_echo("OrderID : ".$NewOrder . "<br>");
		$ExportFileName = "Bestelling_" . $sessionid . "_" . $NewOrder . "_Basis_OBSOLETE.csv" ;
		$ExportFileNeerslag = "Bestelling_" . $sessionid . "_" . $NewOrder . "_Basis_Neerslag_ " . $waarde ; //Zonder CSV omdat we bij uursommen de stationsnaam nog toevoegen
		$ExportFileMakkink = "Bestelling_" . $sessionid . "_" . $NewOrder . "_Basis_Makkink.csv" ;
		$ExportFilePenman = "Bestelling_" . $sessionid . "_" . $NewOrder . "_Basis_Penman.csv" ;

// CONFIGURABLE >>>>>>>>>>
   $ExportPath = "C:/Program Files (x86)/PostgreSQL/EnterpriseDB-Apache/Php/apache/www/meteobase/downloads";
// CONFIGURABLE >>>>>>>>>>
		
		test_echo("----------------------------<BR>");
		$FileCsvNeerslag = $ExportPath . "/" . $ExportFileNeerslag ;
		test_echo("CSV-File (Neerslag) : <br>".$FileCsvNeerslag . "<br>");
		test_echo("----------------------------<BR>");
		$FileCsvMakkink = $ExportPath . "/" . $ExportFileMakkink ;
		test_echo("CSV-File (Makkink) : <br>".$FileCsvMakkink . "<br>");
		test_echo("----------------------------<BR>");
		$FileCsvPenman = $ExportPath . "/" . $ExportFilePenman ;
		test_echo("CSV-File (Penman) : <br>".$FileCsvPenman . "<br>");
		test_echo("--------------------------------------------"."<BR>");
		
		test_echo("<b>ACTUAL EXPORT : </b><br>" );		
		// Determine the temporal window :
		$Day1 = SUBSTR($ValidatedDatum_van,6,4) . SUBSTR($ValidatedDatum_van,0,2) . SUBSTR($ValidatedDatum_van,3,2);
		$Day2 = SUBSTR($ValidatedDatum_tot,6,4) . SUBSTR($ValidatedDatum_tot,0,2) . SUBSTR($ValidatedDatum_tot,3,2);
		test_echo("Datum (start) : ".$Day1 . "<br>");
		test_echo("Datum (stop) : ".$Day2 . "<br>");
	// Perform the exports as demanded :

	// Export precipitation, when required
	ExportPrecipitation($dbHandle, $meteostations, $Day1, $Day2, $area, $FileCsvNeerslag, $waarde, $NamesString, $StationsString, $sessionid, $NewOrder, $neerslag, $makkink, $naam, $mail);

	
	// Export Penman, when required
		//  ****** To be implemented later
//	if ($penman <> "")
//	{
//		ExportPenman();
//	}
	
	test_echo("********************************************"."<BR>");
	test_echo("********************************************"."<BR>");
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
	$Mode = 1; // Mode can be 0 = NoEcho, or 1 = Echo
	if ($Mode == 1)
	{
		echo($EchoString);
	}
}

//This function retrieves the station-numbers from the database.
//  Input is the array that is (manually) selected by the user.
//  Return values (by means of the parameters passed) are :
//     - Fieldnames (makkinkraw.ST334, makkinkraw.ST205) to build the view 
//     - Stationnames (DE_BILT, SOEST) representing the selected stations 
//     - Stationnumbers (ST334, ST205) as keyfields in the query 
function createListStationNumbers($StationArray, $pDbHandle, &$pFieldsString, &$pNamesString, &$pStationString, &$pTimeValue)
{
	// Loop over the array with stations passed as parameter :
	 
	foreach ($StationArray AS $item => $var)
	{
		$Nummer = $var;
		$Naam = $item;
		// Add the station to the fields to be exported, IF not empty.
		if($Nummer != "")
		{
			$pFieldsString = $pFieldsString . 'makkinkraw.st' . $Nummer . ',';
			$pNamesString = $pNamesString . $Naam . ',';
			$pStationString = $pStationString . 'st' .$Nummer . ',';
		}
		else
		{
			echo("Geselecteerd station niet in de database : " . $item . "<BR>");
		}
		test_echo( "Field : " . $pFieldsString . "<BR>");
		test_echo("........................."."<BR>");
	}		
	$pFieldsString = rtrim($pFieldsString,",");
	$pNamesString = rtrim($pNamesString,",");
	$pStationString = rtrim($pStationString,",");
	test_echo( "ALL FIELDS   AS STRING :  " . $pFieldsString . "<BR>");
	test_echo( "ALL NAMES    AS STRING :  " . $pNamesString . "<BR>");
	test_echo( "ALL STATIONS AS STRING :  " . $pStationString . "<BR>");
}

function ExportPrecipitation($pDbHandle, $pStations, $pDay1, $pDay2, $pArea, $pFileCSV , $ExportType, $pNamesString, $pStationsString, $sessionid, $NewOrder, $neerslag, $makkink, $pnaam, $pmail)
{

	test_echo("--------------------------------------------"."<BR>");
	test_echo( "<b>Export Precipitation </b>"  . "<BR>");	
	test_echo( "DatabaseHandle :  <br>" . $pDbHandle . "<BR>");
	test_echo( "Stations    AS String :  <br>" . $pStations . "<BR>");
	test_echo( "StartDay    AS STRING :  <br>" . $pDay1 . "<BR>");
	test_echo( "StopDay     AS STRING :  <br>" . $pDay2 . "<BR>");
	test_echo( "Targetfile    AS STRING :  <br>" . $pFileCSV . "<BR>");
	test_echo( "Day/Hour    AS STRING :  <br>" . $ExportType . "<BR>");
	test_echo("Stations: " . $pStations ." <br>");
	test_echo("Names: " . $pNamesString ." <br>");		
	test_echo("StationsString = " . $pStationsString ." <br>");

	
	$AllStations = explode(",",$pStations);
	test_echo("Number of Stations : " . count($AllStations)."<br>");
		
	// Loop over all stations to retrieve the station key :
	$cmd = '';
    $SelectionStations = '';
	foreach($pStations AS $item => $var) {
        
		$cmd = $cmd . ' ' . $var;
        
	}
	
// Quick and dirty : Add a dummy station
	$SelectionStations = $SelectionStations . "station = 9999"	;	
	test_echo("Stations Selection String : <br>  " . $SelectionStations . "<br>");
// Now export, depending on the temporal request :
	if ($ExportType == "dagstations")
	{
		$cmd =  ' "' . $pmail .'" ' . $cmd;
		$cmd =  ' "' . $pnaam . '" ' . $cmd;
		$cmd = $sessionid . ' ' . $NewOrder . ' ' . $cmd;
		$cmd = 'FALSE ' . $cmd; //makkink
		$cmd = 'TRUE ' . $cmd;	//neerslag
		$cmd = 'TRUE ' . $cmd; //etmaalbasis
		$cmd = ' ' . $pDay1 . ' ' . $pDay2 .  ' ' . $cmd;				
		$cmd = '"c:\Program Files\Hydroconsult\WIWBBASIS\WIWBBASIS.exe" ' . $cmd;
		
		echo($cmd);
		
        $cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
        pclose(popen($cmd, 'r'));
      
    }
	
	if ($ExportType == "uurstations") 
	{
		$cmd =  ' "' . $pmail .'" ' . $cmd;
		$cmd =  ' "' . $pnaam . '" ' . $cmd;
		$cmd = $sessionid . ' ' . $NewOrder . ' ' . $cmd;
		
		if ($makkink <> "")
		{
			$cmd = 'TRUE ' . $cmd;
		}
		else
		{
			$cmd = 'FALSE ' . $cmd;
		}

		if ($neerslag <> "")
		{
			$cmd = 'TRUE ' . $cmd;
		}
		else
		{
			$cmd = 'FALSE ' . $cmd;
		}
		
		$cmd = 'FALSE ' . $cmd;
		
		$cmd = ' ' . $pDay1 . ' ' . $pDay2 .  ' ' . $cmd;
				
		$cmd = '"c:\Program Files\Hydroconsult\WIWBBASIS\WIWBBASIS.exe" ' . $cmd;
		
		echo '<script>alert($cmd)</script>';
				
      die(cmd);
        $cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
        pclose(popen($cmd, 'r'));
	}
}

function ExportPrecipDay($pDbHandle, $pStations, $pDay1, $pDay2, $pFileCSV , $ExportType, $pSelectionStations, $pNamesString, $pStationsString)
{
	// Exports all Stations within the time-frame.	
	// 		Create an array with the station codes :
	CreateStationRope($pStationsString, $StationsNummers, $ArrayStationNummers,$ExportType);
	test_echo("Station Codes :  " . $StationsNummers . "<br>");
	test_echo("Array Station Codes :  " . count($ArrayStationNummers ). "<br>");

	// Prepare the timeframe :
	$DateQuery = "Select DISTINCT datumveld FROM data.precipitation_daily WHERE ";
	$DateQuery = $DateQuery . "datumveld >= " . $pDay1 . " ";   
	$DateQuery = $DateQuery . "AND datumveld <= " . $pDay2 . " "; 
	$DateQuery = $DateQuery . "ORDER BY datumveld ASC";
	test_echo("Date Query  :  " . $DateQuery . "<br>");	
	
	$sResultDate = pg_query($pDbHandle,$DateQuery);
	test_echo("ResultDate :  " . $sResultDate . "<br>");
    $iNumberRowsDate = pg_num_rows($sResultDate);	
	test_echo("Records :  " . $iNumberRowsDate . "<br>");

	// Actaully start the output :
	$OutputFile = fopen($pFileCSV,'w');
	// Write a header...	
	fwrite($OutputFile, 'Meteo Database. Metingen zijn in 1/10 (mm)' . "\r\n");
	fwrite($OutputFile, 'Neerslag (Etmaalsom)' . "\r\n");
	fwrite($OutputFile, 'Datum ,' . $pNamesString . "\r\n");
	fwrite($OutputFile, 'StationCode ,' . $pStationsString . "\r\n");	
	fwrite($OutputFile, 'StationNummer ,' . $StationsNummers . "\r\n");	
	// Loop over ALL dates :
	while ($SingleRowDate = pg_fetch_row($sResultDate))
	{
		// Gather the data for each date, and write it in a single action 
		$DatumItem = trim($SingleRowDate[0] . ",");
		$RainItems = "" ;
		// Assemble the Rain-data for all stations :
		foreach($ArrayStationNummers AS $value)
		{
			// Select the precipitation belonging to the date/station :
			$StationQuery = "SELECT neerslag FROM data.vw_" . $value . "_day WHERE ";    
			$StationQuery = $StationQuery . "datumveld = " . $SingleRowDate[0] . ";"; 
			$sResult = pg_query($pDbHandle,$StationQuery);
			$iNumberRows = pg_num_rows($sResult);	
			$RawResult = trim(pg_fetch_result($sResult,0,0));		
			$RainItems = $RainItems . $RawResult;
		}
		fwrite($OutputFile, $DatumItem . $RainItems .  "\r\n") ;
	}
	// And close the connection.
	fclose($OutputFile);	
}

function CreateStationRope($pStations, $pStationsNummers, $pArrayStationNummers, $pExportType)
{
	// Creates a rope with the station-numbers (st99, st543 --> 99, 543)
	test_echo("***** CreateStationRope: <br>  " . $pStations . "<br>");
	test_echo("*****   " . $pExportType . "<br>");
	$Stations = explode(",",$pStations);
	$Counter = 0 ;
	foreach($Stations AS $value)
	{
		test_echo($value . "<br>");
		$StationString = ltrim($value,"st");
		test_echo("....." . $StationString . "<br>");
		$pStationsNummers = $pStationsNummers . "," . $StationString;
		$pArrayStationNummers[$Counter] = $StationString ;
		$Counter = $Counter + 1 ;
	}	
	$pStationsNummers = ltrim($pStationsNummers,",");
}
    	
//
//		// Prepare the timeframe :
//		test_echo("Geselecteerd stationsnummer is " . $ArrayStationNummers);
//
//		//		fwrite($OutputFile, $value . ' ,');
//		$sResultDate = pg_query($pDbHandle,$DateQuery);
//		test_echo("ResultDate :  " . $sResultDate . "<br>");
//		$iNumberRowsDate = pg_num_rows($sResultDate);	
//		test_echo("Records :  " . $iNumberRowsDate . "<br>");
//	
//		// Loop over ALL dates :
//		// And close the connection.
//		fclose($OutputFile);	
//	}

//}


function GetHourStations($pNamesUC , $pNamesOK , $pNumbersOK , $pDbHandle)
{
	// Stations(hour) must be derived from a separate table :
	$ArrStats = explode("," ,$pNamesUC);
	foreach($ArrStats AS $Station)
	{
		$GoodName = ucwords(strtolower($Station));
		$pNamesOK = $pNamesOK . $GoodName . ",";
		$StationQuery = "SELECT naam, nummer FROM data.uurstations WHERE ";    
		$StationQuery = $StationQuery . "alias = '" . $GoodName  . "';"; 
		$sResult = pg_query($pDbHandle,$StationQuery);	
		$iNumberRows = pg_num_rows($sResult);	
		$RawName = trim(pg_fetch_result($sResult,0,0));		
		$RawNumber = trim(pg_fetch_result($sResult,0,1));		
		$pNumbersOK = $pNumbersOK . $RawNumber . ",";
	}
	$pNamesOK = rtrim($pNamesOK ,",");
	$pNumbersOK = rtrim($pNumbersOK ,",");
	test_echo("Stations (DAY) ==> " . $pNamesOK . "<br>  " );	
	test_echo("Nummers  (DAY) ==> " . $pNumbersOK . "<br>  " );	
}

function ExportMakkink($pDbHandle,$pFields, $pNames, $pStations, $pDay1, $pDay2, $pFileCSV)
{
	test_echo("--------------------------------------------"."<BR>");
	test_echo( "<b>ExportMakkink </b>"  . "<BR>");	
	test_echo( "Stations StNumbers    AS STRING :  <br>" . $pStations . "<BR>");
	test_echo( "Stations StNames      AS STRING :  <br>" . $pNames . "<BR>");
	
	// Filter all stations that do NOT have Makkink data :
	FilterMakkink($pDbHandle, $pStations, $StatMakkink, $FieldsMakkink,$NamesMakkink);
	$TotalMakkinkStations = count($FieldsMakkink);
	test_echo("Makkink Stations : ". $TotalMakkinkStations . "<br>");
	test_echo("Returned.......... : ". $StatMakkink . "<br>");

	foreach($StatMakkink AS $value)
	{
		test_echo($value . "<br>");
	}

	test_echo("Makkink Fields : ". count($FieldsMakkink) . "<br>");
	test_echo("Returned.......... : ". $FieldsMakkink . "<br>");

	foreach($NamesMakkink AS $value)
	{
		test_echo($value . "<br>");
	}

	test_echo("Makkink Names : ". count($NamesMakkink) . "<br>");
	test_echo("Returned.......... : ". $NamesMakkink . "<br>");

	// Assemble the string with Fields for the view and query ($MakkinkFields/$Mfields)...
	foreach($FieldsMakkink AS $value)
	{
		test_echo($value . "<br>");
		$MakkinkFields = $MakkinkFields. "makkinkraw." . $value . ",";
		$Mfields = $Mfields. $value . ",";
	}
	// And for the string with station Names ....
	foreach($NamesMakkink AS $value)
	{
		test_echo($value . "<br>");
		$MNames = $MNames. $value . ",";
	}
	$MakkinkFields = rtrim($MakkinkFields,",");
	$Mfields = rtrim($Mfields,",");
	$MNames = rtrim($MNames,",");
	test_echo("String with Makkink Fields (VIEW): <br>" .$MakkinkFields . "<br>");
	test_echo("String with Makkink Names  (QUERY): <br>" .$MNames . "<br>");
	test_echo("String with Makkink Fields (QUERY): <br>" .$Mfields . "<br>");
	test_echo("........................."."<BR>");
	
	// Set the Query against the table (Makkink) and store results in a VIEW :
	$ExportView = "CREATE OR REPLACE VIEW data.viewmakkink AS ";
	$ExportView = $ExportView . "SELECT makkinkraw.datumveld, " . $MakkinkFields . " ";
	$ExportView = $ExportView . "FROM data.makkinkraw ";
	$ExportView = $ExportView . "WHERE makkinkraw.datumveld >= " . $pDay1 . " ";
	$ExportView = $ExportView . "AND makkinkraw.datumveld <= " . $pDay2 . " ; ";
	$ExportView = $ExportView . "ALTER TABLE data.viewmakkink OWNER TO bas";
	test_echo("Query to construct the VIEW : <br>" . $ExportView . "<br>");
	
	// Perform the query and store it in the view:
    $sResult = pg_query($pDbHandle,$ExportView);
	test_echo("View created when ANY return is given : ". $sResult . "<BR>");
	test_echo("........................."."<BR>");
	
		// Just for fun : report the number of records...
		// Strange. There is a result, BUT no records are returned. Timelag in status database ?
		// Anyway, a fresh query against the view solves this....
		$sQuery = "SELECT datumveld, " . $Mfields . " FROM data.viewmakkink " ;    
		test_echo("Query against the view : <br>". $sQuery . "<BR>");
    	$sResult = pg_query($pDbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
		test_echo("Records in ViewMakkink : ". $iNumberRows . "<BR>");
		test_echo(".........................<br>");
		
		// Write the results to file :
		test_echo("Outputfile : <br>". $pFileCSV . "<BR>");
		test_echo("Names    : <br>". $pNames . "<BR>");
		test_echo("Stations : <br>". $Mfields . "<BR>");
		
		$OutputFile = fopen($pFileCSV,'w');
		// Write a header...
		fwrite($OutputFile, 'Meteo Database. Metingen zijn in 1/10 (mm)' . "\r\n");
		fwrite($OutputFile, 'Verdamping volgens Makkink' . "\r\n");
		fwrite($OutputFile, 'Datum ' . $StatMakkink . "\r\n");
		fwrite($OutputFile, '"  " ,' . $Mfields . "\r\n");
		// and dump all records
		while ($SingleRow = pg_fetch_row($sResult))
		{
			For ($Loop = 0; $Loop <= $TotalMakkinkStations ;$Loop = $Loop+1)
			{
				fwrite($OutputFile, trim($SingleRow[$Loop]). ",") ;
			}
			fwrite($OutputFile, "\r\n");
		}
		fclose($OutputFile);
		test_echo("Output File exited normally....<BR>");
		// That should be about it... So clean up after ourselves ...
    	pg_free_result($result);
    	pg_close($dbHandle);
    	test_echo("----------------------------<BR>");		
}

function ExportPenman()
{
	test_echo("--------------------------------------------"."<BR>");
	test_echo( "ExportPenman"  . "<BR>");	
}

function FilterMakkink($pDbHandle, $pAll, $pMakkink, $pMakkinkFields, $pMakkinkNames)
{
	test_echo("FILTER STATIONS THAT CONTAIN DATA(MAKKINK)<br>");
	test_echo ($pDbHandle."<br>");
	test_echo ($pAll."<br>");
	$AllStations = explode(",",$pAll);
	test_echo("Number of Stations : " . count($AllStations)."<br>");
	test_echo($AllStations[0]."<br>");
	test_echo($AllStations[1]."<br>");
	test_echo($AllStations[2]."<br>");
	test_echo($AllStations[3]."<br>");
	// Get all fields :
	$sQuery = "SELECT * FROM data.makkinkraw WHERE datum = '99/99/1900'" ;    
	test_echo("Query against the view : <br>". $sQuery . "<BR>");
    $sResult = pg_query($pDbHandle,$sQuery);
    $iNumberRows = pg_num_rows($sResult);
	// Should be a single record...
	test_echo("Aantal Records : " . $iNumberRows."<br>");
	// Loop over all fields :
	while($i <= pg_num_fields($sResult))
	{
		// Retrieve the Fieldname :
		$FieldName = pg_field_name($sResult,$i);		
		test_echo("Field : " . $i. "......" . $FieldName ."<br>");	
		// Compare these with all stations :
		For ($Mak=0;$Mak<count($AllStations) ;$Mak=$Mak+1)
		{	
			test_echo("..........Station : " . $Mak. "......" . $AllStations[$Mak] ."<br>");
			if(strcmp($AllStations[$Mak],$FieldName)==0) 
			{
				test_echo("*************************************MATCH<br>");
				$pMakkinkFields[$Mak] = $FieldName;
				$sQueryStName = "SELECT " . $FieldName . " FROM data.makkinkraw WHERE datum = '88/88/1900'" ;    
				$sResultStName = pg_query($pDbHandle,$sQueryStName);
				$iNumberRowsStName = pg_num_rows($sResultStName);
				$RawResultName = trim(pg_fetch_result($sResultStName,0,$FieldName));
				$pMakkink = $pMakkink . "," . $RawResultName ;
				test_echo("Station : " . $RawResultName."<br>");	
			}
		}
		$i=$i+1;
	}
}
function FilterPrecip($pDbHandle, $pAll, $pPrecipitation, $pPrecipFields, $pTimeType)
{
	test_echo("FILTER STATIONS THAT CONTAIN DATA(Precipitation)<br>");
	test_echo ($pDbHandle."<br>");
	test_echo ($pAll."<br>");
	$AllStations = explode(",",$pAll);
	test_echo("Number of Stations : " . count($AllStations)."<br>");
	test_echo($AllStations[0]."<br>");
	test_echo($AllStations[1]."<br>");
	test_echo($AllStations[2]."<br>");
	test_echo($AllStations[3]."<br>");
	// Get all fields, depending on the time switch :
	if ($pTimeType == "dag") $sQuery = "SELECT * FROM data.precipitation_daily WHERE datum = '30/01/1982'" ;    
	if ($pTimeType == "uur") $sQuery = "SELECT * FROM data.precipitation_hourly WHERE datum = '30/01/1982'" ;    
	test_echo("Query against the view : <br>". $sQuery . "<BR>");
    $sResult = pg_query($pDbHandle,$sQuery);
    $iNumberRows = pg_num_rows($sResult);
	// Just to make sure we have a valid query...
	test_echo("Aantal Records : " . $iNumberRows."<br>");
	// Loop over all fields :
	while($i <= pg_num_fields($sResult))
	{
		// Retrieve the Fieldname :
		$FieldName = pg_field_name($sResult,$i);		
		test_echo("Field : " . $i. "......" . $FieldName ."<br>");	
		// Compare these with all stations :
		For ($Prec=0;$Prec<count($AllStations) ;$Prec=$Prec+1)
		{	
			test_echo("..........Station : " . $Prec. "......" . $AllStations[$Prec] ."<br>");
			if(strcmp($AllStations[$Prec],$FieldName)==0) 
			{
				test_echo("*************************************MATCH<br>");
				$pPrecipFields[$Prec] = $FieldName;
				$sQueryStName = "SELECT " . $FieldName . " FROM data.makkinkraw WHERE datum = '88/88/1900'" ;    
				$sResultStName = pg_query($pDbHandle,$sQueryStName);
				$iNumberRowsStName = pg_num_rows($sResultStName);
				$RawResultName = trim(pg_fetch_result($sResultStName,0,$FieldName));
				$pPrecipitation = $pPrecipitation . "," . $RawResultName ;
				test_echo("Station : " . $RawResultName."<br>");	
			}
		}
		$i=$i+1;
	}
}

function GetTimeItems($pArrayStationNummers, $pDay1, $pDateItems,$pDbHandle)
{
// Retrieves the Hour-items for a giver request :
	$Stats = explode(",",$pArrayStationNummers);
	$FirstStation = $Stats[0];
	$Query = "SELECT tijdveld FROM data.precipitation_hourly WHERE station = '" ;
	$Query = $Query . $FirstStation . "' AND datumveld = " . $pDay1 ;
	$sResultDate = pg_query($pDbHandle,$Query);
    $iNumberRowsDate = pg_num_rows($sResultDate);	
	while ($SingleRowDate = pg_fetch_row($sResultDate))
	{
	//	test_echo("TYD :  " . $SingleRowDate[0] . "<br>");	
		$pDateItems = $pDateItems . $SingleRowDate[0] . ",";
		// Gather the data for each date, and write it in a single action 
	}
	$pDateItems = rtrim($pDateItems, ",");
}

?>