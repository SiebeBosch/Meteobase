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
    // ** 2012-02-21
	// ** edited 2012-020-22 by Siebe Bosch
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** FormDataProcessing.php  V6-7
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	echo 'Start Verwerking : ' . $dStartTime . "<BR>". "<BR>";
?>

  <body>

	DAGGEGEVENS <BR>
    <form name = "Etmaalgegevens" action="ScriptProcessEtmaalgegevens.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingEtmaal" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	UURGEGEVENS <BR>
    <form name = "Inlog-Form2" action="ScriptProcessUurgegevens.php" method = "post">
      Naam  <input type="text" name = "InlogNaam2" <BR>
      Bedrijf / Instituut  <input type="text" name = "Bedrijf2" <BR>
      Straatnaam  <input type="text" name = "Straatnaam2" <BR><BR>
      <BR>
      <input type="submit" value = "Importeer"/><br>
NB :  Velden met een * zijn verplicht
    </form>




    </body>
</html>