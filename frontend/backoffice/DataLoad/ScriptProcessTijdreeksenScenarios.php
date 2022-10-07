<html>
  <head>
    <title>Process Tijdreeksen Scenario's</title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This snippet writes the Order-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'Theme'] etc.
    // ** Database-connection is at end-super-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptProcessTijdreeksenScenarios.php      v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright Geopro 2011   www.geopro.nl
    // *********************************************************
    $dStartTime = date_format(date_create(), 'H:i:s') ;
	echo "Process Tijdreeksen Scenario's";
	return "";
?>

  <?php
    // Validating input of the Table Name :
    $ValidatedSessionID = check_input($_POST['SessionID'],"");
    $ValidatedTheme = check_input($_POST['Theme'],"");
  ?>

  <body>
     Keys : <BR>
     <?php 
       echo "Sessie-ID   =" . $ValidatedSessionID . "<BR>";
       echo "Thema       =" . $ValidatedTheme . "<BR>";
     ?>

<?php 
	include('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'local_config.php');

    // ****** Login-data *********
    $sConnectionString="UNSET";
    $dbHandle=0;
    $sMasterUser = MASTERUSER;
    $sMasterPassword = MASTERPASSWORD;
    $sHost = HOST;
    $sPoort =POORT;
    $sDataBase = DATABASE;
    $sTableName = "tijdreeksen";

    // ** Compile the connection-string to the DB:
    $sConnectionString= "host=" .$sHost. " port=" .$sPoort. " user=" .$sMasterUser . " password=" .$sMasterPassword . " dbname=" .$sDataBase ;
//    $sConnectionString= "host=" .$sHost. " port=" .$sPoort. " user=" .$sMasterUser . " password=" .$sMasterPassword ;
    echo "***************** Parameters : *************"."<BR>"; 
    echo $sConnectionString."<BR>";
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
	echo ".....Pingen naar de PostgreSQL server succesvol :" . "<BR>";
    	echo "Database-handle     = ".$dbHandle . "<BR>";
    	echo "PostgreSQL-client   = ".$version['client']. "<BR>";
    	echo "          -protocol = ".$version['protocol']. "<BR>";
    	echo "          -server   = ".$version['server']. "<BR>";
	}

    // Access the table :
    $sQuery = "SELECT * FROM " .$sTableName ;    
    echo "<BR>" . "Query : ". $sQuery . "<BR>";
    
    $sResult = pg_query($dbHandle,$sQuery);
    $iNumberRows = pg_num_rows($sResult);
    echo "<BR>"."Aantal records : ".$iNumberRows;

    // Compile the insertion-string, start with the Keys :
//    $sInsertQuery = "INSERT INTO $sTableName (" ;
//    $sInsertQuery .= "sessionid" . ",";
//    $sInsertQuery .= " loginname" . ",";
//    $sInsertQuery .= " givenstreet" . ",";
//    $sInsertQuery .= " givenzip" . ",";
//    $sInsertQuery .= " giventown" . ",";
//    $sInsertQuery .= " givencountry" . ",";
//    $sInsertQuery .= " givenemail" . ",";
//    $sInsertQuery .= " logindate" . ",";
//    $sInsertQuery .= " logintime" . ",";
//    $sInsertQuery .= " company" . ",";
//    $sInsertQuery .= " housenr" . ",";
//    $sInsertQuery .= " houseext" . ",";
//    $sInsertQuery .= " phone";
    // and add the Values :

//    $sInsertQuery .= ") VALUES (";
//    $sInsertQuery .= ($iNumberRows +1). ",";
//    $sInsertQuery .= "'" . $ValidatedUser . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedStreet . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedZIP . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedTown . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedCountry . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedEmail . "'". ",";
//    $sInsertQuery .= "'" . $dStartDate . "'". ",";
//    $sInsertQuery .= "'" . $dStartTime . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedCompany . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedHouseNr . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedHouseExt . "'". ",";
//    $sInsertQuery .= "'" . $ValidatedPhone. "'";
 
//    $sInsertQuery .= ")";

    echo "<BR>" . $sInsertQuery . "<BR>";

//    $sInsertQuery2= "INSERT INTO ".$sTableName . " (sessionid,loginname) VALUES (" .($iNumberRows + 1).", ". "'NOOT')" ;

    $sInsertQuery2= "INSERT INTO ".$sTableName . " (sessionid,givenemail) VALUES (" .($iNumberRows + 1).", ". "'" . $ValidatedEmail . "')" ;
    
    // Compile the insertion-string, start with the Keys :
    $sInsertKeys = "INSERT INTO $sTableName (" ;
    $sInsertKeys .= "sessionid";
    $sInsertKeys .= "," . " loginname";
    $sInsertKeys .= "," . " givenstreet";
    $sInsertKeys .= "," . " givenzip";
    $sInsertKeys .= "," . " giventown";
    $sInsertKeys .= "," . " givencountry";
    $sInsertKeys .= "," . " givenemail";
    $sInsertKeys .= "," . " logindate";
    $sInsertKeys .= "," . " logintime";
    $sInsertKeys .= "," . " company";
    $sInsertKeys .= "," . " housenr";
    $sInsertKeys .= "," . " houseext";
    $sInsertKeys .= "," . " phone";

    $sInsertKeys .= ")";

echo    "KEYS   = " . $sInsertKeys;

    // Add the Values :
    $sInsertValues  = " VALUES (";
    $sInsertValues .= ($iNumberRows +1) ;
    $sInsertValues .= "," . "'" . $ValidatedUser . "'";
    $sInsertValues .= "," .  "'" . $ValidatedStreet . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedZIP . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedTown . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedCountry . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedEmail . "'";  
    $sInsertValues .= "," .  "'" . $dStartDate . "'";  
    $sInsertValues .= "," .  "'" . $dStartTime . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedCompany . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedHouseNr . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedHouseExt . "'";  
    $sInsertValues .= "," .  "'" . $ValidatedPhone . "'";  
    $sInsertValues .=  ")";

echo    "<BR>"."VALUES = " . $sInsertValues ;

    // And combine :
    $sInsertQuery = $sInsertKeys . $sInsertValues ;

    echo "<BR>"."<BR>".$sInsertQuery . "<BR>";

//    echo "<BR>" . $sInsertQuery2 . "<BR>";


//    $bResult = pg_query($dbHandle, $sInsertQuery);

        if (!$bResult)
        {
          echo "<BR>"."Geen Result";
        }
        else
        {
          echo "<BR>"."Resulaat : ". $bResult . "<BR>";
        }

    // ** Clean-up :
    pg_free_result($result);
    pg_close($dbHandle);
    echo "<BR>";
?>

  </body>
AFGEVUURD
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
?>

