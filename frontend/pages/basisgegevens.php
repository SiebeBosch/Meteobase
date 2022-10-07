<div id="gegevenspaneel_aanmelden">
<?php
// Meteo Database
// Version 6-7
// April 2012

$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subbg'])){
		
		// Verwerken van het formulier
		$gegevenstype = '';
		if(isset($_POST['neerslag'])){
			$neerslag = $_POST['neerslag'];
			$gegevenstype.= $neerslag . ' ';
		}
		if(isset($_POST['makkink'])){
			$makkink = $_POST['makkink'];
			$gegevenstype.= $makkink . ' ';
		}
		$ms_check = TRUE;
		$waarde = $_POST['stationsType'];
		$datum_van = $_POST['fromDate'];
		$datum_tot = $_POST['toDate'];
		$area = 6; //Gebiedsreductiefactor verwijderd! $_POST['area'];
		$gebruiker = $_COOKIE['gebruiker'];
		$mail = $gebruiker['mail'];
		$naam = $gebruiker['naam'];
		$sessionid = $gebruiker['sessionid'];
		$ms_go = TRUE;
		if(isset($_COOKIE['ms'])){
			$meteostations = $_COOKIE['ms'];
		}
		include('backoffice_basisgegevens.php');
		
        $adres = 'http://62.148.170.210/meteobase/downloads/';
        $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';		
		$bericht = 'Geachte ' . $naam . ',</p><p> Uw bestelling met sessie-ID ' . $sessionid . ' wordt klaargezet op ' . $link . '. <br> U ontvangt een e-mail met een rechtstreekse download-link.<br>';
		$data = 'Gegevenstype: ' . $gegevenstype . '<br>Tijdswaarde: ' . $waarde . '<br>Datum: ' . $datum_van . ' tot ' . $datum_tot . '<br>';
        $_SESSION['dataFeedbackMsg'] = "send message";
        include('pages/basis01.php');
}else{
    include('pages/basis01.php');
}
?>	
</div>