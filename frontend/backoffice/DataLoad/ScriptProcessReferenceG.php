<html>
  <head>
    <title>Process Referentie G</title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet writes the Reference-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'Theme'] etc.
    // ** Database-connection is at end-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptProcessReferenceG.php       v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2011   www.geopro.nl
    // *********************************************************


    // Set some globals :
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	$RecordCounter = 0;
	$InvalidCounter = 0;
	$ErrorCounter = 0;
	$Wetnesses ;

	$UserFromForm = $_POST['Gebruiker'];
	$RemarkFromForm = $_POST['OpmerkingRegioG'];
	$StartLineFromForm = $_POST['LineFrom'];
	$StopLineFromForm = (integer)$_POST['LineTo'];
// *************************************************************************
//      Foldername is hard-coded :                                      // *
	$FolderHardCoded = "D:/Data/WorkData/TXT/ReeksenPerRegio";      // *
	$FileHardCoded = "Regio_G_1906-2010.txt";                       // *
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

	$BatchGrootte = 10000;
	$Batches = round((($StopValue-$StartValue)/$BatchGrootte),0,PHP_ROUND_HALF_DOWN);
	$Rest = ($StopValue-$StartValue) % $BatchGrootte ;

	echo "Start = " . $StartValue . "<br>";
	echo "Stop  = " . $StopValue . "<br>";
	echo "Batchgrootte : " . $BatchGrootte  . "<br>";
	echo "Batches : " . $Batches . "<br>";
	echo "Rest    : " . $Rest . "<br>";
	$BatchCounter = 1;
	for ($b = 1 ; $b <= ($Batches +1);$b++)
	{
		echo "BATCH ============ " . $BatchCounter . "<br>";

		for ($i = ((($BatchCounter-1)  * $BatchGrootte) + $StartValue) ; $i < (($BatchCounter  * $BatchGrootte) + $StartValue) ;$i++)
		{

		if ($i <= $StopValue)
		{
			$LineCounter = $LineCounter + 1;

				$items = explode(",",$linearray[$i]);
		
				$ExtraG  = trim($items[0]) ;
				$Date    = trim($items[1]) ;
				$Hour    = trim($items[2]) ;
				$Wetness = trim($items[3]) ;

			    // Check if we have valid records :
				if (check_record($ExtraG , $Date , $Hour , $Wetness) == "FALSE")
				{
					echo "Onvolledig Record in regel : " . $LineCounter . "<br>";
					echo "-  ExtraG " . $ExtraG    . "<br>";
					echo "-  Date   " . $Date      . "<br>";
					echo "-  Hour   " . $Hour      . "<br>";
					echo "-  Wet    " . $Wetness   . "<br>";
					$InvalidCounter = $InvalidCounter + 1;
				}
				else
				{
					add_record($ExtraG , $Date , $Hour , $Wetness, $RecordCounter);
					$RecordCounter = $RecordCounter  + 1;
				}
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
function check_record($pExtraG , $pDate , $pHour , $pWetness)
{
// This function checks if a station is valid :
	if (strlen($pExtraG  ) < 1) return "FALSE";
	if (strlen($pDate    ) < 8) return "FALSE";
	if (strlen($pHour    ) < 1) return "FALSE";
	if (strlen($pWetness ) < 1) return "FALSE";
	return "TRUE";
}

//-------------------------------------------------------------------
function add_record( $pExtraG , $pDate , $pHour , $pWetness,$pRecordCounter)
{
// This function adds a new record to the Wetnesses-array :
	global $Wetnesses ;
	$NewIndex = $pRecordCounter ;
	$NewRecord = Array($pExtraG , $pDate , $pHour , $pWetness);
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
	$Keys[0] = "extrag";
	$Keys[1] = "datum";
	$Keys[2] = "time";
	$Keys[3] = "hourg";

	$Key = assemble_keys($Keys,'data.regiongraw');

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
    	$sQuery = "SELECT * FROM data.regiongraw" ;

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
			$ErrorCounter= $ErrorCounter + 1;
			$ErrorCounter = $ErrorCounter + 1;
			echo "Fout bij Insert van " . $Values[0];
			echo pg_last_error() . "<br>";
			echo $sInsertQuery . "<br>";
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



