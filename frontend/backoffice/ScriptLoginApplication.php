<html>
  <head>
    <title>Script Login Application </title>
  </head>

<?php 
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This script writes the login-data into the PostgreSQL-RDBMS
    // ** The database/table are is hard-coded, fields are read from the form 
    // **      that calls this file [   Field : 'naam'] etc.
    // ** Database-connection is at end-user level, and is compiled 
    // ** from defaults
    // **
    // ** 2012-04-10
    // ** ScriptLoginApplication.php   V-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************
    $dStartDate = date_format(date_create(), 'Y-m-d') ;
    $dStartTime = date_format(date_create(), 'H:i:s') ;
?>

  <?php
    // Validating input of the Table Name :
    $ValidatedUser = check_input($naam,"");
    $ValidatedCompany = check_input($org,"");
    $ValidatedPhone = "0600000000";
    $ValidatedEmail = check_input($mail,"");

  ?>

  <body>

<?php 
	include('local_config.php');

    // ****** Login-data *********
    	$sConnectionString="UNSET";
    	$dbHandle=0;
    	$sMasterUser = MASTERUSER;
    	$sMasterPassword = MASTERPASSWORD;
    	$sHost = HOST;
    	$sPoort =POORT;
    	$sDataBase = DATABASE;
    	$sTableName = "tbaanvrager";

    // ** Compile the connection-string to the DB:
    	$sConnectionString= "host=" .$sHost. " port=" .$sPoort. " user=" .$sMasterUser . " password=" .$sMasterPassword . " dbname=" .$sDataBase ;
		test_echo("***************** Parameters : *************"."<BR>"); 
		test_echo($sConnectionString."<BR>");

    // ** and connect to the database Server:
    	$dbHandle= pg_connect($sConnectionString);

    // ** Test for access to the database :
    	IF (!pg_ping($dbHandle) )
		{
    		// (PHP 8: geen pg_free_result op een verbinding)
    		pg_close($dbHandle);
		exit;
		}
	ELSE
		{
        	$version = pg_version($dbHandle);
			test_echo(".....Pingen naar de PostgreSQL server succesvol :" . "<BR>");
			test_echo("Database-handle     = (verbinding)<BR>");
			test_echo("PostgreSQL-client   = ".$version['client']. "<BR>");
			test_echo("          -protocol = ".$version['protocol']. "<BR>");
			test_echo("          -server   = ".$version['server']. "<BR>");
		}

    // New session id from the sequence (see next_id in sanitize.php)
		require_once __DIR__ . '/../sanitize.php';
		$sessionid = next_id($dbHandle, SEQ_SESSIONID);
		test_echo("<BR>"."Nieuw sessie-ID : ".$sessionid);

    // Compile the insertion-string, start with the Keys :
    	$sInsertQuery = "INSERT INTO $sTableName (" ;
    	$sInsertQuery .= "sessionid" . ",";
    	$sInsertQuery .= " naam" . ",";
    	$sInsertQuery .= " email" . ",";
    	$sInsertQuery .= " logindatum" . ",";
    	$sInsertQuery .= " logintijd" . ",";
    	$sInsertQuery .= " organisatie" . ",";
    	$sInsertQuery .= " telefoon";
    // and add the Values :

    	$sInsertQuery .= ") VALUES (";
    	$sInsertQuery .= $sessionid . ",";
    	$sInsertQuery .= "'" . $ValidatedUser . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedEmail . "'". ",";
    	$sInsertQuery .= "'" . $dStartDate . "'". ",";
    	$sInsertQuery .= "'" . $dStartTime . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedCompany . "'". ",";
    	$sInsertQuery .= "'" . $ValidatedPhone. "'";
    	$sInsertQuery .= ")";

		test_echo("<BR>" . $sInsertQuery . "<BR>");

		$sInsertQuery2= "INSERT INTO ".$sTableName . " (sessionid,givenemail) VALUES (" .$sessionid.", ". "'" . $ValidatedEmail . "')" ;
    
    // Compile the insertion-string, start with the Keys :
    	$sInsertKeys = "INSERT INTO $sTableName (" ;
    	$sInsertKeys .= "sessionid";
    	$sInsertKeys .= "," . " naam";
    	$sInsertKeys .= "," . " email";
    	$sInsertKeys .= "," . " logindatum";
    	$sInsertKeys .= "," . " logintijd";
    	$sInsertKeys .= "," . " organisatie";
    	$sInsertKeys .= "," . " telefoon";
    	$sInsertKeys .= ")";

		test_echo("KEYS   = " . $sInsertKeys);

    // Add the Values :
    	$sInsertValues  = " VALUES (";
    	$sInsertValues .= "$1" ;
    	$sInsertValues .= "," . "$2";
    	$sInsertValues .= "," . "$3";  
    	$sInsertValues .= "," . "$4";  
    	$sInsertValues .= "," . "$5";  
    	$sInsertValues .= "," . "$6" ;  
    	$sInsertValues .= "," . "$7" ;  
    	$sInsertValues .=  ")";

		test_echo("<BR>"."VALUES = " . $sInsertValues) ;

    // And combine :
    	$sInsertQuery = $sInsertKeys . $sInsertValues ;
		test_echo("<BR>"."<BR>".$sInsertQuery . "<BR>");
		$bResult = false;
		if ($sessionid !== null && pg_prepare($dbHandle, "logininsert", $sInsertQuery) !== false) {
			$bResult =  pg_execute($dbHandle, "logininsert", array(
				$sessionid,
				$ValidatedUser,
				$ValidatedEmail,
				$dStartDate,
				$dStartTime,
				$ValidatedCompany,
				$ValidatedPhone
			));
		}

    // Only a stored registration gets a session id; index.php shows an error message otherwise
		$registratieGelukt = ($bResult !== false);
        if ($registratieGelukt)
        	{
			setcookie('gebruiker[sessionid]', (string)$sessionid);
          	test_echo("<BR>Resulaat : OK<BR>");
          	pg_free_result($bResult);
        	}
        else
        	{
          	error_log('Meteobase: registratie niet opgeslagen (sessie ' . var_export($sessionid, true) . '): ' . pg_last_error($dbHandle));
        	}
    // ** Clean-up :
    	pg_close($dbHandle);
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

