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
    // ** 2012-02-21
	// ** Renamed to v6.5 by Siebe Bosch on 2012-02-22
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** ScriptProcessEtmaalgegevens.php       v6-7
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************
?>

<?php
    // Fetch the relevant data from content from the calling form :
	$UserFromForm = $_POST['Gebruiker'];
	$RemarkFromForm = $_POST['OpmerkingEtmaal'];
    // And set some globals :
	$LinesHeader = 24;
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
    	$dCurrentDate = date_format(date_create(), 'Y-m-d') ;
// *************************************************************************
//      Foldername and line-offset are hard-coded :                     // *
	$FolderFromForm = "D:/Data/WorkData/Testdata";                  // *
//      This folder can NOT contain subfolders .... Somehow...          // *
// *************************************************************************

    // Some globals 
	$g_Debug = 0 ;        // debug mode; 0 = silent, 1++ = comment is on
    // for the insertion into the DB :
	$g_StationNumber = 0;
	$g_StationName = "";
	$g_Date = "";
	$g_Rain = 0;
	$g_Snow = 0;
	$g_Trash = "";

    // User-info :
	echo "Datum             : " . $dCurrentDate. "<BR>";
	echo "Start Verwerking  : " . $dStartTime . "<BR>";
	echo "Gebruikersnaam    : " . $UserFromForm . "<BR>";
	echo "Folder            : " . $FolderFromForm . "<BR>";
	echo "Opmerking         : " . $RemarkFromForm . "<BR>";
	echo "Regels Header     : " . $LinesHeader . "<BR>";
	echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br>";
?>

  <body>

<?php 
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

   // Now record this action into the database, to keep track of all imports :

	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "postgis" ;

	$Keys[0] = "loaddate";
	$Keys[1] = "loadtime";
	$Keys[2] = "loaduser";
	$Keys[3] = "loadremark";
	$Keys[4] = "loadtable";
	$Keys[5] = "folder";
	$Keys[6] = "files";
	$Keys[7] = "records";
	$Keys[8] = "errors";

	$Values[0] = "'" . $dCurrentDate . "'";   
	$Values[1] = "'" . $dStartTime . "'";   
	$Values[2] = "'" . $UserFromForm . "'";    
	$Values[3] = "'" . $RemarkFromForm . "'"; 
	$Values[4] = "'" . $FolderFromForm . "'"; 
	$Values[5] = "'" . "precipitation_daily" . "'"; 
	$Values[6] = $ProcessedFiles ; 
	$Values[7] = $ProcessedRecords ; 
	$Values[8] = $lProcessedErrors ; 

	$Res = insert_record($Keys, $Values , $DbAccess, "data.dataloads");
	debug_echo ($Res. "<br>");
	echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br>";
?>
  </body>
</html>


<?php
// -------------------------------------------------------------------------------
// This function processes the file passed as parameter:
function process_file($pFilename, $pFirstline)
{
	global $ProcessedLines;
	global $lProcessedErrors;
	global $ProcessedRecords;
	global $g_Date;
	global $g_Rain;
	global $g_Snow;
	global $g_StationNumber;

    	global $dStartTime;
    	global $dCurrentDate;

	$LineCounter = 0;
	$RecordCounter = 0;
	$NegCounter = 0;
	$pLogline = "Processing " . $pFilename ;
	$currentfile = $pFilename;
	$linearray = file($currentfile);

	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "postgis" ;

	$Keys[0] = "station";
	$Keys[1] = "datum";
	$Keys[2] = "neerslag";
	$Keys[3] = "sneeuw";
	$Keys[4] = "bron";


    // Read the file passed as parameter and put it into an array of its lines.
    // Loop through these and process them all :
	for ($i = 0; $i<=count($linearray);$i++)
	{
	    // Have a line-counter running to eliminate the header-info :
		$LineCounter = $LineCounter + 1;
		if ($i < $pFirstline)
		{
			$NegCounter = $NegCounter + 1;
			if ($i = $pFirstline - 1)
			{
				if (trim($linearray[$i]) !== "STN,YYYYMMDD,   RD,   SX,")
				{
					$pErrorline = $pErrorline . "Mogelijke fout in Headerfile : <br>" ;
					$pErrorline = $pErrorline . "Regel " . $i . " : ".  $linearray[$i];
					$lProcessedErrors = $lProcessedErrors + 1;
				}
			}		
		}
		else
		{
		    // Start processing the actual data :
			dissect_line($linearray[$i]);
			$RecordCounter = $RecordCounter + 1;
		    // And insert the items into a new record :
			$Values[0] = $g_StationNumber;   
			$Values[1] = "'" . $g_Date . "'" ; 
			$Values[2] = "'" . $g_Rain . "'" ; 
			$Values[3] = "'" . $g_Snow . "'" ; 
  			$Values[4] = "'Import " . $dCurrentDate . " + " . $dStartTime . "'"; 
			$Res = insert_record($Keys, $Values , $DbAccess, "data.precipitation_daily");
			debug_echo( $Res);
    			debug_echo( "----------------------------------  <BR>");
		}
	}
	
	$ProcessedLines = $ProcessedLines + count($linearray);
	$ProcessedRecords = $ProcessedRecords + $RecordCounter ;
	echo "    LOG        : " . $pLogline . "<br>";
	echo "    ERROR      : " . $pErrorline . "<br>";
	echo "    Lines      : " . count($linearray)  . "<br>";
	echo "    Records    : " . $RecordCounter . "<br>";
	echo "    Errors     : " . $lProcessedErrors. "<br>";
	echo "    Neglected  : " . $NegCounter . "<br>";
	echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++ <br>";
}
?>

<?php
// -------------------------------------------------------------------------------
function dissect_line($pLine)
// This function dissects the line into its constituting items :
{
    // Strip the line from all whitespace :
	$wLine = trim($pLine);
	settype($wLine, "string");
    // PRESUMING that this is a fixed position file ...

    // Some globals for the insertion into the DB :
	global $g_StationNumber ;
	global $g_StationName ;
	global $g_Date ;
	global $g_Rain ;
	global $g_Snow ;
	global $g_Trash ;

	if ($wLine[0].$wLine[1].$wLine[2]=="")
	{
		debug_echo( "EMPTY INPUT  <br>");
	}
	else
	{
		$g_StationNumber = $wLine[0].$wLine[1].$wLine[2];
		$g_Date = $wLine[4].$wLine[5].$wLine[6].$wLine[7].$wLine[8].$wLine[9].$wLine[10].$wLine[11];
		$g_Rain = $wLine[13] . $wLine[14] . $wLine[15] . $wLine[16] . $wLine[17];
		$g_Rain = $g_Rain * 0.10 ;
		$g_Snow = (integer)( $wLine[19]. $wLine[20]. $wLine[21]. $wLine[22]. $wLine[23]);
		$g_StationName = $wLine[25].$wLine[26].$wLine[27].$wLine[28].$wLine[29];
	}
}
?>

<?php
// -------------------------------------------------------------------------------
function insert_record($pKeys, $pValues, $pDbAccess, $pTable)
{

    // This function inserts a record passed as parameter into the database.
    // Table/fields and login-info is also passed

    // Reference the Error-counter :
	global $lProcessedErrors;
	$lProcessedErrors = $lProcessedErrors + 1;

    // Access the Database :
	$DbAccess = assemble_dbaccess($pDbAccess);
	debug_echo( $DbAccess . "<br>" );
    	$dbHandle= pg_connect($DbAccess);
    // Test for access to the database :
    	IF (!pg_ping($dbHandle) )
	{
		debug_echo( "Geen PostgreSQL server verbinding" . "<BR>" );
    		pg_free_result($dbHandle);
    		pg_close($dbHandle);
		exit;
	}
	ELSE
	{
        	$version = pg_version($dbHandle);
		debug_echo( ".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
    		debug_echo( "Database-handle     = ".$dbHandle . "<BR>");
    		debug_echo( "PostgreSQL-client   = ".$version['client']. "<BR>");
    		debug_echo( "          -protocol = ".$version['protocol']. "<BR>");
    		debug_echo( "          -server   = ".$version['server']. "<BR>". "<BR>");
	}

    // Access the table :
    	$sQuery = "SELECT * FROM " . $pTable ;    
    	debug_echo( "Query : ". $sQuery . "<BR>");
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
   	debug_echo( "Aantal records : ".$iNumberRows. "<BR>". "<BR>");

    // Assemble the Insert-query :
	$Keys = assemble_keys($pKeys, $pTable);
	$Values = assemble_values($pValues);
    	$sInsertQuery = $Keys . $Values ;
    	debug_echo( $sInsertQuery . "<BR>");

    // Attempt the Insert :
    	$bResult = pg_query($dbHandle, $sInsertQuery);
    // Clean-up :
    	pg_free_result($result);
    	pg_close($dbHandle);
    // Return the result of the insert :
        if (!$bResult)
        {
		return "Geen Result". "<BR>";
        }
        else
        {
		$lProcessedErrors = $lProcessedErrors - 1;
		return "Resulaat : ". $bResult . "<BR>";
        }
}
?>

<?php
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
?>

<?php
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
?>

<?php
// -------------------------------------------------------------------------------
function assemble_values($pValues)
// Assembles the string reprerenting the VALUES :
{
    // This function will assemble the VALUES for insertion into the database :
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
?>

<?php
// -------------------------------------------------------------------------------
function debug_echo($pEchoString)
// Will echo, depending on the (global) value of $g_Debug
{
	global $g_Debug;
	if ($g_Debug == 0)
	{
		// Echo is off ...
	}
	else
	{
		echo $pEchoString;
	}
}
?>