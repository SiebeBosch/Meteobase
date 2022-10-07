<html>
  <head>
    <title>Process Daggegevens </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This scriptfile (batch-) processes the daily precipitation data
    // **     from text-file
    // ** It is supposed to be called from FormDataProcessing.php
    // **  
    // ** 
    // **
    // ** 2012-04-10
    // ** ScriptProcessEtmaalgegevens.php       v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************

    // Set some globals :
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	$RecordCounter = 0;
	$InvalidCounter = 0;
	$ErrorCounter = 0;
	$Wetnesses ;

	$UserFromForm = $_POST['GebruikerEtmaal'];
	$RemarkFromForm = $_POST['OpmerkingEtmaal'];
// *************************************************************************
//      Foldername is hard-coded :                                      // *
	$FolderFromForm = "D:/Data/WorkData/Testdata";                  // *
//      This folder can NOT contain subfolders .... Somehow...          // *
// *************************************************************************

    // Compile the file to be processed....
	$File = $FolderFromForm ;

	echo "<b><u>Neerslag (Dagwaarden)</b></u>" . "<br>";
	echo "Start         : " . $dStartTime . "<br>";
	echo "Gebruiker     : " . $UserFromForm . "<br>";
	echo "Opmerkingen   : " . $RemarkFromForm . "<br>";
	echo "Folder (HC)   : " . $FolderFromForm  . "<br>";
	echo "------------------------------- " . "<br>";

    // Loop over the folder passed as the working directory :
	$ProcessedFiles = 0;
	$ProcessedLines = 0;
	$ProcessedRecords = 0;
	$lProcessedErrors = 0;

	$Dir = dir($FolderFromForm);
	while (($file = $Dir -> read()) !==false)
	{
		if ($file != '.' && $file != '..' )
		{
			$wFile = $FolderFromForm . '/' . $file;
			process_file ($wFile, $LinesHeader);
			$ProcessedFiles = $ProcessedFiles + 1;
		}
	}
	$Dir -> close();
    	$dStopTime = date_format(date_create(), 'H:i:s') ;
	echo "Verwerking Gereed ......" . $dStopTime  . " <br>";
	echo "Processed Files ........" . $ProcessedFiles . " <br>";
	echo "Processed Lines ........" . $ProcessedLines . " <br>";
	echo "Valid Records .........." . $ProcessedRecords . " <br>";
	echo "Errors in Procession ..." . $lProcessedErrors . " <br>";

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

echo $pFilename . "<br>";

// This function processes the file passed as parameter :
    // Declare the globals used :
	global $RecordCounter ;
	global $InvalidCounter ;
	global $ErrorCounter ;
	global $Wetnesses ;

    // Set some local variables :
	$LineCounter = 0;
	$linearray = file($pFilename);
	echo "Lines : " . count($linearray) . "<br>";

	$StartValue = 0;
	$StopValue = count($linearray);

	$BatchGrootte = 5000;
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
//		echo "BATCH ============ " . $BatchCounter . "<br>";
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
//			echo "************** Gegevens opgeslagen in de database  <br>";
		 }
		 else
		 {
//			echo "Error in dumping ...  <br>";
		 }


		$Wetnesses ="";
		$BatchCounter =$BatchCounter +1;
	}

	echo "LINES......" . $LineCounter ."<br>";
	return "TRUE";
}

//-------------------------------------------------------------------
function add_record( $pLinearray,$pRecordCounter)
{
// This function adds a new record to the Wetnesses-array :
	global $Wetnesses ;

//echo $pLinearray . "<br>";
$pRecordCounter = $pRecordCounter + 1 ;


	$NewIndex = $pRecordCounter ;
	$items = explode(",",$pLinearray);
	$Station    = trim($items[0]) ;
	$Date  = trim($items[1]) ;
	$Rain  = trim($items[2]) ;
	$Snow  = trim($items[3]) ;
	$St_280  = trim($items[4]) ;

	$NewRecord = Array($Station , $Date , $Rain , $Snow);
	$Wetnesses[$NewIndex ] = $NewRecord ;
}

//-------------------------------------------------------------------
function dump_array()
{
// This function dumps the Reference-array into the database :
	global $Wetnesses ;
	global $ErrorCounter;
	global $RecordCounter;
	$CurrentRecord ;

    // These are the parameters for database-access :
	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "meteobase" ;

    // Assemble the Keys for the insertion :
	$Keys[0] = "station";
	$Keys[1] = "datum";
	$Keys[2] = "neerslag";
	$Keys[3] = "sneeuw";

	$Key = assemble_keys($Keys,'data.precipitation_daily');

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
    	$sQuery = "SELECT * FROM data.precipitation_daily" ;
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
    	
	foreach ($Wetnesses as $CurrentRecord )
	{
		$LineCounter = $LineCounter + 1;
		$Values[0] = "'". trim($CurrentRecord[0]) . "'";
		$Values[1] = "'". trim($CurrentRecord[1]) . "'";
		$Values[2] = "'". trim($CurrentRecord[2]) . "'";
		$Values[3] = "'". trim($CurrentRecord[3]) . "'";

		$Value = assemble_values($Values) ;
    		$sInsertQuery = $Key . $Value ;
   	    // Attempt the Insert :

    		$bResult = pg_query($dbHandle, $sInsertQuery);
		if ($bResult == "")
		{
			$ErrorCounter = $ErrorCounter + 1;
//			echo "Fout bij Insert van " . $Values[0];
//			echo pg_last_error() . "<br>";
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



