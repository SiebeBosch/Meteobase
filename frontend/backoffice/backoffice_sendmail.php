<html>
  <head>
    <title>Neerslag Database --> MAIL Form</title>
  </head>

<?php
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This code sends a mail-message 
    // ** This script is calles from backoffice_assemblemail.php
    // **
    // ** The php.ini file MUST contain information on the mail-program to be used.    
    // ** And this MUST be installed and working properly of course
    // ** 
    // ** 2012-04-10    v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************


// Assemble the mail-message :


    $pTo = $_POST['Email'];
    $pSender = $_POST['Sender'];
    $pSubject = $_POST['Subject'];
    $pMessage = $_POST['Message'];

	$Header = "From :" . pSender;

echo '' . $pTo . ' --- ' . $pSubject  . ' +++ ' . $pMessage . ' oooo '. $Header;

	// mail ($pTo, $pSubject, $pMessage);



?>

  <body>


    </body>
</htm