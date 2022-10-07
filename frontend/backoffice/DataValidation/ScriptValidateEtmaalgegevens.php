<html>
  <head>
    <title>Validate Daggegevens </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This scriptfile (batch-) validates the daily precipitation data
    // **     from text-file
    // ** It is supposed to be called from FormDataValidation.php
    // **  
    // ** 
    // **
    // ** 2012-04-10
    // ** ScriptValidateEtmaalgegevens.php       v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************

    // Set some globals :
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	$StationCounter = 0;
        echo $dStartTime . "<br><br>";
    // Connect to the Database :
    // These are the parameters for database-access :
	$DbAccess[0]= "localhost";
	$DbAccess[1]= "5432";
	$DbAccess[2]= "arend";
	$DbAccess[3]= "arend";
	$DbAccess[4]= "meteobase" ;
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
    	$sQuery = "SELECT * FROM meta.dagstation" ;
    	$sResult = pg_query($dbHandle,$sQuery);
    	$iNumberRows = pg_num_rows($sResult);
    	
    	echo "Records : ".     $iNumberRows . "<br>";
    // Loop over the stations :
        for ($b = 0 ; $b <= ($iNumberRows -1 );$b++)
        {
            $StationCounter = $b + 1;
            $Station = pg_fetch_array($sResult,$b);
         // Retrieve the Station-key :
            $StationKey = $Station['number'];
            echo "<b>Station :             " . $StationKey . "</b><br>";
            $StationQuery = "SELECT * FROM data.precipitation_daily WHERE station = " . $StationKey;
            $ResultSet = pg_query($dbHandle,$StationQuery);
         // Now count the records delivered :
            $CountedRecords = pg_num_rows($ResultSet);
            echo "Records   =    " . $CountedRecords . "<br>";
         // Get the number of NULL-values in the precipitation-field :
            $QuerySumNull = "SELECT COUNT(neerslag) FROM data.precipitation_daily WHERE LENGTH(neerslag) = 0 ";
            $QuerySumNull = $QuerySumNull . " AND station = " . $StationKey ;
            $ResultSet = pg_query($dbHandle,$QuerySumNull);
            $NullCounts = pg_fetch_array($ResultSet,0);
            $NullCount = $NullCounts[0] ;
            echo "Null-records     =    " .   $NullCount . "<br>";
         // Get the number of filled values in the precipitation-field :
            $QueryFilledRecords = "SELECT COUNT(neerslag) FROM data.precipitation_daily WHERE LENGTH(neerslag) > 0 ";
            $QueryFilledRecords = $QueryFilledRecords . " AND station = " . $StationKey ;
            $ResultSet = pg_query($dbHandle,$QueryFilledRecords);
            $FilledCounts = pg_fetch_array($ResultSet,0);
            $FilledCount  = $FilledCounts [0] ;
            echo "Filled records   =    " .   $FilledCount . "<br>";
         // And fetch the first date mentioned :
            $QueryMinDate = "SELECT MIN(datum) FROM data.precipitation_daily WHERE station = " . $StationKey;
            $QueryMinDate = $QueryMinDate . " AND LENGTH(neerslag) > 0 ";
            $ResultSet = pg_query($dbHandle,$QueryMinDate);
            $MinValue = pg_fetch_array($ResultSet,0);
            $MinDate = $MinValue[0] ;
            echo "First Date =    " . $MinDate . "<br>";
         // And fetch the last date mentioned :
            $QueryMaxDate = "SELECT MAX(datum) FROM data.precipitation_daily WHERE station = " . $StationKey;
            $QueryMaxDate = $QueryMaxDate . " AND LENGTH(neerslag) > 0 ";
            $ResultSet = pg_query($dbHandle,$QueryMaxDate);
            $MaxValue = pg_fetch_array($ResultSet,0);
            $MaxDate = $MaxValue[0] ;
            echo "Last Date =    " . $MaxDate . "<br>";
         // Retrieve the Minimum precipitation :
            $QuerySumRain = "SELECT MIN(CAST(neerslag AS numeric)) FROM data.precipitation_daily WHERE station = " . $StationKey;
            $QuerySumRain = $QuerySumRain . " AND LENGTH(neerslag) > 0 ";
            $ResultSet = pg_query($dbHandle,$QuerySumRain);
            $MinValue = pg_fetch_array($ResultSet,0);
            $MinRainValue = ROUND($MinValue[0],4) ;
            echo "Minimum =    " .  $MinRainValue . "<br>";
         // Retrieve the Maximum precipitation :
            $QueryMaxRain = "SELECT MAX(CAST(neerslag AS numeric)) FROM data.precipitation_daily WHERE station = " . $StationKey;
            $QueryMaxRain = $QueryMaxRain . " AND LENGTH(neerslag) > 0 ";
            $ResultSet = pg_query($dbHandle,$QueryMaxRain);
            $MaxValue = pg_fetch_array($ResultSet,0);
            $MaxRainValue = ROUND($MaxValue[0],4) ;
            echo "Maximum =    " .   $MaxRainValue . "<br>";
         // Calculate the total precipitation :
            $QuerySumRain = "SELECT SUM(CAST(neerslag AS numeric)) FROM data.precipitation_daily WHERE station = " . $StationKey;
            $QuerySumRain = $QuerySumRain . " AND LENGTH(neerslag) > 0 ";
            $ResultSet = pg_query($dbHandle,$QuerySumRain);
            $SumValue = pg_fetch_array($ResultSet,0);
            $SumRainValue = $SumValue[0] ;
            echo "Total Rain =    " .   $SumRainValue . "<br>";
         // Calculate the average (daily) precipitation :
            $AvPrec = ROUND(($SumRainValue / $FilledCount),3) ;
            echo "Daily Rain =    " .   $AvPrec . "<br>";
         // Calculate the average (yearly) precipitation :
            $AvPrecYear = $AvPrec * 365 ;
            echo "Yearly Rain =    " .   $AvPrecYear . "<br>";
            $UpdateQuery = "UPDATE meta.dagstation SET records = " . $CountedRecords ;
            $UpdateQuery = $UpdateQuery . ", firstdate = " . $MinDate ;
            $UpdateQuery = $UpdateQuery . ", lastdate = " . $MaxDate ;
            $UpdateQuery = $UpdateQuery . ", sumrain = " . $SumRainValue ;
            $UpdateQuery = $UpdateQuery . ", avrain = " . $AvPrec ;
            $UpdateQuery = $UpdateQuery . ", minval = " . $MinRainValue  ;
            $UpdateQuery = $UpdateQuery . ", maxval = " . $MaxRainValue ;
            $UpdateQuery = $UpdateQuery . ", nullrecords = " . $NullCount ;
            $UpdateQuery = $UpdateQuery . ", filledrecords = " . $FilledCount ;
            $UpdateQuery = $UpdateQuery . ", rainyear = " . $AvPrecYear ;
            $UpdateQuery = $UpdateQuery . " WHERE number = " . $StationKey ;
            $UpdateResult = pg_query($dbHandle,$UpdateQuery);
            echo "--------------" . "<br>";
        }

    // Close the Meta-database :
         pg_free_result($dbHandle); 
         pg_close($dbHandle);

    	$dStopTime = date_format(date_create(), 'H:i:s') ;
	echo "Stations .........." . $StationCounter . " <br>";
	$dStopTime = date_format(date_create(), 'H:i:s') ;
	echo "<br>" . $dStopTime;

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


//-------------------------------------------------------------------
?>
AFGEVUURD
</html>



