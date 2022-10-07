<html>
  	<head>
    		<title>Neerslag Database --> Data Processing Nulbestanden</title>
  	</head>

<?php
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This form will feed the delivered data into the database :
    // **    it will call various php-scripts
    // ** 
    // ** 
    // ** 
    // **
    // ** 2012-04-10
    // ** FormDataProcessing.php  V-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	echo 'Start Verwerking : ' . $dStartTime . "<BR>". "<BR>";
?>

  <body>

	STATIONS <BR>
    <form name = "Stations" action="ScriptProcessStations.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingEtmaal" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	DAGGEGEVENS - Station <BR>
    <form name = "Etmaalgegevens" action="ScriptProcessEtmaalgegevens.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerEtmaal" <BR>
      Opmerking <input type="text" name = "OpmerkingEtmaal" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	UURGEGEVENS - Station <BR>
    <form name = "Uurgegevens" action="ScriptProcessUurgegevens.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerUur" <BR>
      Opmerking <input type="text" name = "OpmerkingUur" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	VERDAMPING (Makkink) <BR>
    <form name = "Makkink" action="ScriptProcessMakkink.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerMakkink" <BR>
      Opmerking <input type="text" name = "OpmerkingMakkink" <BR>
      Startlijn <input type="text" name = "StartMakkink" <BR>
      Stoplijn  <input type="text" name = "StopMakkink" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>


	VERDAMPING (Penman)<BR>
    <form name = "Penman" action="ScriptProcessPenman.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerPenman" <BR>
      Opmerking <input type="text" name = "OpmerkingPenman" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	REFERENTIE - Regio <BR>
    <form name = "Referentie" action="FormReferentieData.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerReferentie" <BR>
      Opmerking <input type="text" name = "OpmerkingReferentie" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	STOCHASTEN <BR>
    <form name = "Stochasten" action="ScriptProcessStochasten.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerStochasten" <BR>
      Opmerking <input type="text" name = "OpmerkingStochasten" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	TIJDREEKSEN <BR>
    <form name = "Tijdreeksen" action="ScriptProcessTijdreeksen.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerTijdreeksen" <BR>
      Opmerking <input type="text" name = "OpmerkingTijdreeksen" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	TIJDREEKSENSCENARIOS <BR>
    <form name = "Tijdreeksen" action="ScriptProcessTijdreeksenScenarios.php" method = "post">
      Gebruiker <input type="text" name = "GebruikerTijdreeksenScenarios" <BR>
      Opmerking <input type="text" name = "OpmerkingTijdreeksenScenarios" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>



    </body>
</html>