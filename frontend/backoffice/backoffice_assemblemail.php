<html>
  <head>
    <title>Neerslag Database --> MAIL Form</title>
  </head>

<?php
    // *********************************************************
    // ** "Neerslag"-database for Hydroconsult
    // **
    // ** This code assembles a mail-message 
    // ** 
    // ** The php.ini file MUST contain information on the mail-program to be used.    
    // **  
    // ** 
    // **
    // ** 2012-04-10    v-67
	// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
    // ** Copyright GeoPro 2012   www.geopro.nl
    // *********************************************************

// Assemble the mail-message :

?>


  <body>

    <form name = "Mail-Form" action="backoffice_sendmail.php" method = "post">;
      * email <input type="text" name = "Email" <BR>;
      verzonden <input type="text" name = "Sender" <BR>;
      onderwerp <input type="text" name = "Subject" <BR>;
      boodschap <input type="text" name = "Message" <BR>;
      <BR>
      <input type="submit" value = "Verstuur"/><br>;
NB :  Velden met een * zijn verplicht
    </form>




    </body>
</htm