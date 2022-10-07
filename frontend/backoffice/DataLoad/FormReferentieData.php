<html>
  	<head>
    		<title>Neerslag Database --> Data Processing Referentiebestanden</title>
  	</head>

<?php
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This form will import the delivered Reference Data into the database :
    // **    it will call various php-scripts
    // ** 
    // ** 
    // ** 
    // **
    // ** 2012-04-10
    // ** FormReferentieData.php  V-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************
    	$dStartTime = date_format(date_create(), 'H:i:s') ;
	echo "<b><u>Referentiedata</b></u>". "<BR>";
	echo 'Start Verwerking : ' . $dStartTime . "<BR>". "<BR>";
?>

  <body>

	Uurgegevens Regio G <BR>
    <form name = "Stations" action="ScriptProcessReferenceG.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingRegioG" <BR>
      Van Regel <input type="text" name = "LineFrom" <BR>
      Tot Regel <input type="text" name = "LineTo" <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	Uurgegevens Regio H <BR>
    <form name = "Stations" action="ScriptProcessReferenceH.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingEtmaal" <BR>
      Van Regel <input type="text" name = "LineFrom" <BR>
      Tot Regel <input type="text" name = "LineTo" <BR>      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	Uurgegevens Regio H+ <BR>
    <form name = "Stations" action="ScriptProcessReferenceP.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingEtmaal" <BR>
      Van Regel <input type="text" name = "LineFrom" <BR>
      Tot Regel <input type="text" name = "LineTo" <BR>      <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	Uurgegevens Regio L <BR>
    <form name = "Stations" action="ScriptProcessReferenceL.php" method = "post">
      Gebruiker <input type="text" name = "Gebruiker" <BR>
      Opmerking <input type="text" name = "OpmerkingRegioL" <BR>
      Van Regel <input type="text" name = "LineFrom" <BR>
      Tot Regel <input type="text" name = "LineTo" <BR>      <BR>
      <BR>
      <input type="submit" value = "Importeer"/><br><br>
    </form>

	Samenvoegen <BR>
    <form name = "Stations" action="ScriptCombineReferences.php" method = "post">
      <BR>
      <input type="submit" value = "Voer Uit"/><br><br>
    </form>






    </body>
</html>