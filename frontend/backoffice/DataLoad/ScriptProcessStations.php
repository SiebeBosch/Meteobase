<html>
  <head>
    <title>Process Stations </title>
  </head>

<?php 

    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This script imports the Stations into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'User'] etc.
    // ** Database-connection is at end-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptProcessStations.php      v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2011   www.geopro.nl
    // *********************************************************

    // Set some globals :
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
		$RecordCounter = 0;
		$InvalidCounter = 0;
		$ErrorCounter = 0;
		$Stations;

		$UserFromForm = $_POST['Gebruiker'];
		$RemarkFromForm = $_POST['OpmerkingEtmaal'];
// *************************************************************************
//      Foldername is hard-coded :                                      // *
		$FolderHardCoded = "D:/Data/WorkData/TXT";                      // *
		$FileHardCoded = "Stations.txt";                                // *
//      This folder can NOT contain subfolders .... Somehow...          // *
// *************************************************************************

    // Compile the file to be processed....
	$File = $FolderHardCoded . "/" . $FileHardCoded ;

	echo "<b><u>Stations</b></u>" . "<br>";
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
	echo "ARRAY......" . count($Stations) . "<br>";

    // And eventually dump the array with stations into the database :
	if (dump_array() == 0)
	{
		echo "************** Gegevens opgeslagen in de database  <br>";
	}
	else
	{
		echo "Error in dumping ...  <br>";
	}
?>
</html>

<?php 
//-------------------------------------------------------------------
function check_station($pNumber , $pPhi , $pLab , $pName)
{
// This function checks if a station is valid :
	if (strlen($pNumber) <= 2) return "FALSE";
	if (strlen($pPhi   ) <= 2) return "FALSE";
	if (strlen($pLab   ) <= 2) return "FALSE";
	if (strlen($pName  ) <= 2) return "FALSE";
	return "TRUE";
}
//-------------------------------------------------------------------
function add_station( $pNumber , $pPhi , $pLab , $pName)
{
// This function adds a new station to the Stations-array :
	global $Stations ;

	$NewIndex = count($Stations) ;
	$NewStation = Array($pNumber,$pPhi,$pLab , $pName);
	$Stations[$NewIndex ] = $NewStation ;
}
//-------------------------------------------------------------------
function process_file($pFilename)
{
// This function processes the file passed as parameter :
    // Declare the globals used :
	global $RecordCounter ;
	global $InvalidCounter ;
	global $ErrorCounter ;
	global $Stations ;

    // Set some local variables :
	$LineCounter = 0;
	$linearray = file($pFilename);

	for ($i = 0; $i<=count($linearray);$i++)
	{
		$LineCounter = $LineCounter + 1;
		$items = explode(",",$linearray[$i]);

		$Number = trim($items[0]);
		$Lab = trim($items[1]) . "," . trim($items[2]);
		$Phi = trim($items[3]) . "," . trim($items[4]);
		$Name = trim($items[5]);
 
	    // Check if we have a complete station :
		if (check_station($Number , $Phi , $Lab , $Name) == "FALSE")
		{
			echo "Onvolledig Station in regel : " . $LineCounter . "<br>";
			echo "-  Number" . $Number . "<br>";
			echo "-  Phi   " . $Phi    . "<br>";
			echo "-  Lab   " . $Lab    . "<br>";
			echo "-  Name  " . $Name   . "<br>";
			$InvalidCounter = $InvalidCounter + 1;
		}
		else
		{
			add_station($Number , $Phi , $Lab , $Name);
			$RecordCounter = $RecordCounter  + 1;
		}
	}

	echo "LINES......" . $LineCounter ."<br>";
	return "TRUE";
}
//-------------------------------------------------------------------
function dump_array()
{
// This function dumps the Stations-array into the database :
	global $Stations ;
	global $ErrorCounter;
	$RecordCounter =0;
	$ErrorCounter =0;
	$CurrentStation ;

    // These are the parameters for database-access :
	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "meteobase" ;

    // Assemble the Keys for the insertion :
	$Keys[0] = "nummer";
	$Keys[1] = "naam";
	$Keys[2] = "phi";
	$Keys[3] = "lab";
	$Key = assemble_keys($Keys,"data.stationswiwb");

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
    	$sQuery = "SELECT * FROM data.stationswiwb" ;    
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);

	foreach ($Stations as $CurrentStation )
	{
		$LineCounter = $RecordCounter + 1;
		$Values[0] = (integer) strip_double_quote($CurrentStation[0]);
		$Values[1] = "'". strip_double_quote($CurrentStation[3]) . "'";
		$Values[2] = "'". strip_double_quote($CurrentStation[1]) . "'";
		$Values[3] = "'". strip_double_quote($CurrentStation[2]) . "'";
		$Value = assemble_values($Values) ;
    		$sInsertQuery = $Key . $Value ;

   	    // Attempt the Insert :
    		$bResult = pg_query($dbHandle, $sInsertQuery);
		if ($bResult == "FALSE")
		{
			$ErrorCounter= $ErrorCounter + 1;
			$ErrorCounter = $ErrorCounter + 1;
			echo "Fout bij Insert van " . $Values[0];
		}
	}

    // Clean-up :
    	pg_free_result($result);
    	pg_close($dbHandle);

	return $ErrorCounter ;

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
function strip_double_quote($pContaminatedString)
// Removes double quotes (") from a string, if present :
{
	$Processed = ltrim($pContaminatedString, '"');
	$Processed = rtrim($Processed , '"');
	return $Processed ;
}
// -------------------------------------------------------------------------------
?>