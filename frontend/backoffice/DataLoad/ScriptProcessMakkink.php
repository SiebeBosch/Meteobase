<html>
  <head>
    <title>Process Makkink</title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet writes the Makkink-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'Theme'] etc.
    // ** Database-connection is at end-super-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptProcessMakkink.php       v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2011   www.geopro.nl
    // *********************************************************


    // Set some globals :
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	$RecordCounter = 0;
	$InvalidCounter = 0;
	$ErrorCounter = 0;
	$Wetnesses ;

	$UserFromForm = $_POST['GebruikerMakkink'];
	$RemarkFromForm = $_POST['OpmerkingMakkink'];
	$StartLineFromForm = $_POST['StartMakkink'];
	$StopLineFromForm = (integer)$_POST['StopMakkink'];
// *************************************************************************
//      Foldername is hard-coded :                                      // *
	$FolderHardCoded = "D:\METEOBASE\Dataload\Makkink";      // *
	$FileHardCoded = "VerdampingMakkinkNIEUW.txt";                       // *
//      This folder can NOT contain subfolders .... Somehow...          // *
// *************************************************************************

    // Compile the file to be processed....
	$File = $FolderHardCoded . "/" . $FileHardCoded ;

	echo "<b><u>Stations</b></u>" . "<br>";
	echo "Start         : " . $dStartTime . "<br>";
	echo "Gebruiker     : " . $UserFromForm . "<br>";
	echo "Opmerkingen   : " . $RemarkFromForm . "<br>";
	echo "Folder (HC)   : " . $FolderHardCoded . "<br>";
	echo "File (HC)     : " . $FileHardCoded . "<br>";
	echo "File          : " . $File . "<br>". "<br>";

    // Open the file and process it :
	if (process_file($File) == TRUE)
	{
		echo "************** Bestand succesvol verwerkt  <br>";
	}
	else
	{
		echo "Error in processing ...  <br>";
	}
    // Report the resumption :
	echo "RECORDS...." . $RecordCounter . "<br>";
	echo "INVALID...." . $InvalidCounter . "<br>";
	echo "ERRORS....." . $ErrorCounter . "<br>";
	echo "ARRAY......" . count($Wetnesses) . "<br>";

	$dStopTime = date_format(date_create(), 'H:i:s') ;
	echo "<br>" . $dStopTime;

//-------------------------------------------------------------------
function process_file($pFilename)
{
// This function processes the file passed as parameter :
    // Declare the globals used :
	global $RecordCounter ;
	global $InvalidCounter ;
	global $StartLineFromForm ;
	global $StopLineFromForm ;
	global $ErrorCounter ;
	global $Wetnesses ;

    // Set some local variables :
	$LineCounter = 0;
	$linearray = file($pFilename);
	echo "Lines : " . count($linearray) . "<br>";

    // Determine the range of the records hat need to be imported ...
	echo $StartLineFromForm . "<br>";
	echo $StopLineFromForm . "<br>";



	$StartValue = 0;
	if ($StartLineFromForm > 0) {$StartValue = (integer)$StartLineFromForm;}
	$StopValue = count($linearray);
	if ((integer)$StopLineFromForm  < count($linearray)) {$StopValue = (integer)$StopLineFromForm ;}

	$BatchGrootte = 100;
	$Batches = round((($StopValue-$StartValue)/$BatchGrootte),0,PHP_ROUND_HALF_DOWN);
	$Rest = ($StopValue-$StartValue) % $BatchGrootte ;

	echo "Start = " . $StartValue . "<br>";
	echo "Stop  = " . $StopValue . "<br>";
	echo "Batchgrootte : " . $BatchGrootte  . "<br>";
	echo "Batches : " . $Batches . "<br>";
	echo "Rest    : " . $Rest . "<br>";

	$BatchCounter = 1;

	for ($b = 0 ; $b <= ($Batches +1);$b++)
	{
		echo "BATCH ============ " . $BatchCounter . "<br>";
		for ($i = ((($BatchCounter-1)  * $BatchGrootte) + $StartValue) ; $i < (($BatchCounter  * $BatchGrootte) + $StartValue) ;$i++)
		{
			if ($i <= $StopValue)
			{
			   $LineCounter = $LineCounter + 1;
                           add_record($linearray[$i], $RecordCounter);
			   $RecordCounter = $RecordCounter  + 1;
			    // Quit when the counter exceeds the maximum value stated :
				if ($LineCounter >= $StopLineFromFromForm ) $LineCounter == count($linearray);
			}
		}

   	    // And dump the array with stations into the database :
		if (dump_array() == 0)
		{
			echo "************** Gegevens opgeslagen in de database  <br>";
		}
		else
		{
			echo "Error in dumping ...  <br>";
		}
		$Wetnesses ="";
		$BatchCounter =$BatchCounter +1;
	}

	echo "LINES......" . $LineCounter ."<br>";
	return "TRUE";
}

//-------------------------------------------------------------------
function check_record($pExtraH , $pDate , $pHour , $pWetness)
{
// This function checks if a station is valid :
	if (strlen($pExtraH  ) < 1) return "FALSE";
	if (strlen($pDate    ) < 8) return "FALSE";
	if (strlen($pHour    ) < 1) return "FALSE";
	if (strlen($pWetness ) < 1) return "FALSE";
	return "TRUE";
}

//-------------------------------------------------------------------
function add_record( $pLinearray,$pRecordCounter)
{
// This function adds a new record to the Wetnesses-array :
	global $Wetnesses ;

	$NewIndex = $pRecordCounter ;
	$items = explode(",",$pLinearray);
	$Date    = trim($items[0]) ;
	$St_260  = trim($items[1]) ;
	$St_235  = trim($items[2]) ;
	$St_265  = trim($items[3]) ;
	$St_280  = trim($items[4]) ;
	$St_310  = trim($items[5]) ;
	$St_380  = trim($items[6]) ;
	$St_210  = trim($items[7]) ;
	$St_240   = trim($items[8]) ;
	$St_270   = trim($items[9]) ;
	$St_275   = trim($items[10]) ;
	$St_290   = trim($items[11]) ;
	$St_350   = trim($items[12]) ;
	$St_370   = trim($items[13]) ;
	$St_375   = trim($items[14]) ;
	$St_344   = trim($items[15]) ;
	$St_225   = trim($items[16]) ;
	$St_330   = trim($items[17]) ;
	$St_348   = trim($items[18]) ;
	$St_242   = trim($items[19]) ;
	$St_249   = trim($items[20]) ;
	$St_251   = trim($items[21]) ;
	$St_257   = trim($items[22]) ;
	$St_267   = trim($items[23]) ;
	$St_269   = trim($items[24]) ;
	$St_273   = trim($items[25]) ;
	$St_277   = trim($items[26]) ;
	$St_278   = trim($items[27]) ;
	$St_279   = trim($items[28]) ;
	$St_283   = trim($items[29]) ;
	$St_286   = trim($items[30]) ;
	$St_319   = trim($items[31]) ;
	$St_323   = trim($items[32]) ;
	$St_356   = trim($items[33]) ;
	$St_340   = trim($items[34]) ;
	$St_377   = trim($items[35]) ;
	$St_391   = trim($items[36]) ;
	$Balance  = trim($items[37]) ;

	$NewRecord = Array($Date,$St_260,$St_235,$St_265,$St_280,$St_310,$St_380,$St_210,$St_240,$St_270,$St_275,$St_290,$St_350,$St_370,$St_375,$St_344,$St_225,$St_330,$St_348,$St_242,$St_249,$St_251,$St_257,$St_267,$St_269,$St_273,$St_277,$St_278,$St_279,$St_283,$St_286,$St_319,$St_323,$St_356,$St_340,$St_377,$St_391,$Balance);
	$Wetnesses[$NewIndex ] = $NewRecord ;
}

//-------------------------------------------------------------------
function dump_array()
{
// This function dumps the Reference-array into the database :
	global $Wetnesses ;
	global $ErrorCounter;
	$RecordCounter =0;
	$ErrorCounter =0;
	$CurrentRecord ;

    // These are the parameters for database-access :
	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "meteobase" ;

    // Assemble the Keys for the insertion :
	$Keys[0] = "datum";
	$Keys[1] = "st260";
	$Keys[2] = "st235";
	$Keys[3] = "st265";
	$Keys[4] = "st280";
	$Keys[5] = "st310";
	$Keys[6] = "st380";
	$Keys[7] = "st210";
	$Keys[8] = "st240";
	$Keys[9] = "st270";
	$Keys[10] = "st275";
	$Keys[11] = "st290";
	$Keys[12] = "st350";
	$Keys[13] = "st370";
	$Keys[14] = "st375";
	$Keys[15] = "st344";
	$Keys[16] = "st225";
	$Keys[17] = "st330";
	$Keys[18] = "st348";
	$Keys[19] = "st242";
	$Keys[20] = "st249";
	$Keys[21] = "st251";
	$Keys[22] = "st257";
	$Keys[23] = "st267";
	$Keys[24] = "st269";
	$Keys[25] = "st273";
	$Keys[26] = "st277";
	$Keys[27] = "st278";
	$Keys[28] = "st279";
	$Keys[29] = "st283";
	$Keys[30] = "st286";
	$Keys[31] = "st319";
	$Keys[32] = "st323";
	$Keys[33] = "st356";
	$Keys[34] = "st340";
	$Keys[35] = "st377";
	$Keys[36] = "st391";
	$Keys[37] = "balance";

	$Key = assemble_keys($Keys,'data.makkinkraw');

    // Access the Database :
	$DbAccessString = assemble_dbaccess($DbAccess);
    	$dbHandle= pg_connect($DbAccessString );
    // Test for access to the database :
    	IF (!pg_ping($dbHandle) )
	{
		echo "Geen PostgreSQL server verbinding" . "<BR>";
    		pg_free_result($dbHandle);
    		pg_close($dbHandle);
		exit;
	}
    // Access the table :
    	$sQuery = "SELECT * FROM data.makkinkraw" ;
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);

	foreach ($Wetnesses as $CurrentRecord )
	{
		$LineCounter = $LineCounter + 1;
		$Values[0] = "'". trim($CurrentRecord[0]) . "'";
		$Values[1] = "'". trim($CurrentRecord[1]) . "'";
		$Values[2] = "'". trim($CurrentRecord[2]) . "'";
		$Values[3] = "'". trim($CurrentRecord[3]) . "'";
		$Values[4] = "'". trim($CurrentRecord[4]) . "'";
		$Values[5] = "'". trim($CurrentRecord[5]) . "'";
		$Values[6] = "'". trim($CurrentRecord[6]) . "'";
		$Values[7] = "'". trim($CurrentRecord[7]) . "'";
		$Values[8] = "'". trim($CurrentRecord[8]) . "'";
		$Values[9] = "'". trim($CurrentRecord[9]) . "'";
		$Values[10] = "'". trim($CurrentRecord[10]) . "'";
		$Values[11] = "'". trim($CurrentRecord[11]) . "'";
		$Values[12] = "'". trim($CurrentRecord[12]) . "'";
		$Values[13] = "'". trim($CurrentRecord[13]) . "'";
		$Values[14] = "'". trim($CurrentRecord[14]) . "'";
		$Values[15] = "'". trim($CurrentRecord[15]) . "'";
		$Values[16] = "'". trim($CurrentRecord[16]) . "'";
		$Values[17] = "'". trim($CurrentRecord[17]) . "'";
		$Values[18] = "'". trim($CurrentRecord[18]) . "'";
		$Values[19] = "'". trim($CurrentRecord[19]) . "'";
		$Values[20] = "'". trim($CurrentRecord[20]) . "'";
		$Values[21] = "'". trim($CurrentRecord[21]) . "'";
		$Values[22] = "'". trim($CurrentRecord[22]) . "'";
		$Values[23] = "'". trim($CurrentRecord[23]) . "'";
		$Values[24] = "'". trim($CurrentRecord[24]) . "'";
		$Values[25] = "'". trim($CurrentRecord[25]) . "'";
		$Values[26] = "'". trim($CurrentRecord[26]) . "'";
		$Values[27] = "'". trim($CurrentRecord[27]) . "'";
		$Values[28] = "'". trim($CurrentRecord[28]) . "'";
		$Values[29] = "'". trim($CurrentRecord[29]) . "'";
		$Values[30] = "'". trim($CurrentRecord[30]) . "'";
		$Values[31] = "'". trim($CurrentRecord[31]) . "'";
		$Values[32] = "'". trim($CurrentRecord[32]) . "'";
		$Values[33] = "'". trim($CurrentRecord[33]) . "'";
		$Values[34] = "'". trim($CurrentRecord[34]) . "'";
		$Values[35] = "'". trim($CurrentRecord[35]) . "'";
		$Values[36] = "'". trim($CurrentRecord[36]) . "'";
		$Values[37] = "'". trim($CurrentRecord[37]) . "'";

		$Value = assemble_values($Values) ;

    		$sInsertQuery = $Key . $Value ;
   	    // Attempt the Insert :
    		$bResult = pg_query($dbHandle, $sInsertQuery);
		if ($bResult == "")
		{
			$ErrorCounter= $ErrorCounter + 1;
			$ErrorCounter = $ErrorCounter + 1;
			echo "Fout bij Insert van " . $Values[0];
			echo pg_last_error() . "<br>";
		}
	}

    // Clean-up :
    	pg_free_result($result);
    	pg_close($dbHandle);
	return $ErrorCounter ;
}

// -------------------------------------------------------------------------------
function assemble_keys($pKeys, $pTable)
// Assembles the string reprerenting the KEYS :
{
    // This function will assemble the keys for insertion into the database :
	$CountKeys = 0;
	$FinalString = "INSERT INTO " . $pTable . "(";
	foreach($pKeys as $item)
	{
		$FinalString = $FinalString . $item ;
		$CountKeys = $CountKeys + 1;
		if ($CountKeys < count($pKeys)) $FinalString = $FinalString .",";
	}
	$FinalString = $FinalString .")";
	return $FinalString;
}

// -------------------------------------------------------------------------------
function assemble_values($pValues)
// Assembles the string reprerenting the VALUES :
{
	$FinalString = " VALUES (";
	$CountValues = 0;
	foreach($pValues as $item)
	{
		$FinalString = $FinalString . $item ;
		$CountValues = $CountValues + 1;
		if ($CountValues < count($pValues)) $FinalString = $FinalString .",";
	}
	$FinalString = $FinalString .")";
	return $FinalString;
}

// -------------------------------------------------------------------------------
function assemble_dbaccess($pDbAccess)
// Assembles the Database-connection string :
{
    // This function assembles the database-connection string :
	$FinalString = "host=" . $pDbAccess[0];
	$FinalString = $FinalString  . " port=" . $pDbAccess[1];
	$FinalString = $FinalString  . " user=" . $pDbAccess[2];
	$FinalString = $FinalString  . " password=" . $pDbAccess[3];
	$FinalString = $FinalString  . " dbname=" . $pDbAccess[4];
	return $FinalString;
}

//-------------------------------------------------------------------
?>
AFGEVUURD
</html>



