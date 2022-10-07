<html>
  <head>
    <title>Combineer Referentiegegevens </title>
  </head>

<?php 
	include('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'local_config.php');

    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet combines the Reference-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded
    // **
    // ** Database-connection is at end-user level, and is compiled
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptCombineReferences.php      v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2011   www.geopro.nl
    // *********************************************************
        $dStartTime = date_format(date_create(), 'H:i:s') ;

     // Set the Globals :
        $CurrentDayData;
        $CurrentDay;
        $CurrentTime;
        $HourG;
        $HourH;
        $HourHP;
        $HourL;
        $ExtraG;
        $ExtraH;
        $ExtraHP;
        $ExtraL;

     // Open the database for writing :

    // These are the parameters for database-access :
	$DbAccess[0]= HOST;
	$DbAccess[1]= POORT;
	$DbAccess[2]= MASTERUSER;
	$DbAccess[3]= MASTERPASSWORD;
	$DbAccess[4]= DATABASE;

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
    // Access the common table :
    	$sQueryCommon = "SELECT * FROM data.regionhour" ;
    	$sResult = pg_query($dbHandle,$sQueryCommon);
    	$iNumberRows = pg_num_rows($sResult);
        echo "DBACCESS : " . $iNumberRows . "<br>";

echo "Loop over de table regiohour <br>";
//     for ($i = 1 ; $i < $iNumberRows ;$i++)
     for ($i = 50 ; $i < 100 ;$i++)
     {
         // Retrieve the day and time ...
         fetch_daytime ($dbHandle , $i);
         // Get the associated data from the H-dataset :
         fetch_H ($dbHandle , $CurrentDay , $CurrentTime);
         // the associated data from the H+-dataset ...
         fetch_HPlus ($dbHandle , $CurrentDay , $CurrentTime);
         // and the associated data from the L-dataset :
         fetch_L ($dbHandle , $CurrentDay , $CurrentTime);
         echo ".    H      : " . $HourH . "<br>";
         echo ".    ExtraH : " . $ExtraH . "<br>";
         echo ".    H+     : " . $HourHP . "<br>";
         echo ".    ExtraH+: " . $ExtraHP . "<br>";
         echo ".    L      : " . $HourL . "<br>";
         echo ".    ExtraL : " . $ExtraL . "<br>";
         
         // Check the result (globals) :
         echo "Voer checks uit <br>";
         check_data($pResult);
echo "Houdt de stats bij <br>";
echo "Update Record <br>";
         // Reset the globals used ...
         reset_globals();
     }



echo "Geef stats weer <br>";

        

        
        
        

        
        
        
    // Clean-up :
    	pg_free_result($result);
    	pg_close($dbHandle);
        
return "";

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
function fetch_daytime($pDbHandle, $pID)
// Fetches the day/time from the record concerned :
{
        global $CurrentDayData;
        global $CurrentDay;
        global $CurrentTime;

        $SelString = "SELECT datum,hour FROM data.regionhour WHERE id = " . $pID;
//        echo $SelString . "" . "<br>";
       	$CurrentDayData = pg_query($pDbHandle,$SelString);
       	$CurrentRecord = pg_fetch_array($CurrentDayData);
        $CurrentDay = $CurrentRecord[0];
        $CurrentTime = $CurrentRecord[1];
}

// -------------------------------------------------------------------------------
function fetch_H($pDbHandle, $pDate, $pTime)
// Fetches the attributes from the HourH-table :
{
        global $HourH;
        global $ExtraH;
//echo "Date   " . $pDate . "<br>";
//echo "Time   " . $pTime . "<br>";
        $SelString = "SELECT hourh,extrah FROM data.regionhraw WHERE datum = '" . $pDate . "' AND time = '" . $pTime . "'";
//        echo $SelString . "" . "<br>";
       	$CurrentDayData = pg_query($pDbHandle,$SelString);
       	$CurrentRecord = pg_fetch_array($CurrentDayData);
        $HourH = $CurrentRecord[0];
        $ExtraH = $CurrentRecord[1];
}

// -------------------------------------------------------------------------------
function fetch_HPlus($pDbHandle, $pDate, $pTime)
// Fetches the attributes from the HourH-Plus-table :
{
        global $HourHP;
        global $ExtraHP;
        $SelString = "SELECT hourhp,extrahp FROM data.regionhpraw WHERE datum = '" . $pDate . "' AND time = '" . $pTime . "'";
       	$CurrentDayData = pg_query($pDbHandle,$SelString);
       	$CurrentRecord = pg_fetch_array($CurrentDayData);
        $HourHP = $CurrentRecord[0];
        $ExtraHP = $CurrentRecord[1];
}

// -------------------------------------------------------------------------------
function fetch_L($pDbHandle, $pDate, $pTime)
// Fetches the attributes from the HourL-table :
{
        global $HourL;
        global $ExtraL;
        $SelString = "SELECT hourl,extral FROM data.regionlraw WHERE datum = '" . $pDate . "' AND time = '" . $pTime . "'";
       	$CurrentDayData = pg_query($pDbHandle,$SelString);
       	$CurrentRecord = pg_fetch_array($CurrentDayData);
        $HourL = $CurrentRecord[0];
        $ExtraL = $CurrentRecord[1];
}
// -------------------------------------------------------------------------------
function check_data($pResult)
// Checks the data retrieved from th etables.
//      Uses  :
{







$pResult = "OK";



}

// -------------------------------------------------------------------------------
function reset_globals()
// Resets all globals used ....
{
        global $CurrentDay;
        global $CurrentTime;
        global $HourG;
        global $HourH;
        global $HourHP;
        global $HourL;
        global $ExtraG;
        global $ExtraH;
        global $ExtraHP;
        global $ExtraL;

        $CurrentDay = "" ;
        $CurrentTime = "" ;
        $HourG = "" ;
        $HourH = "" ;
        $HourHP = "" ;
        $HourL = "" ;
        $ExtraG = "" ;
        $ExtraH = "" ;
        $ExtraHP = "" ;
        $ExtraL = "" ;
}

//-------------------------------------------------------------------

?>

