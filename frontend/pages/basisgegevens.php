<div id="gegevenspaneel_aanmelden">
<?php
// Meteo Database
// Version 6-7
// April 2012

$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subbg'])){
	// CSRF validation
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		http_response_code(403);
		exit('Security validation failed');
	}
	
	// Honeypot check
	if (!empty($_POST['website_url_hp'])) {
		exit(); 
	}
	
	// Time-based check
	$min_submission_time = 3;
	$form_start_time = isset($_POST['form_timestamp']) ? (int)$_POST['form_timestamp'] : 0;
	if ($form_start_time === 0 || (time() - $form_start_time) < $min_submission_time) {
		exit(); // Silent fail - too fast
	}
	
	// Rate limiting - max 5 downloads per 10 minutes
	$max_downloads = 5;
	$time_window = 600; // 10 minutes
	
	if (!isset($_SESSION['basis_download_attempts'])) {
		$_SESSION['basis_download_attempts'] = [];
	}
	
	// Clean old attempts
	$_SESSION['basis_download_attempts'] = array_filter(
		$_SESSION['basis_download_attempts'],
		function($timestamp) use ($time_window) {
			return (time() - $timestamp) < $time_window;
		}
	);
	
	// Check if limit exceeded
	if (count($_SESSION['basis_download_attempts']) >= $max_downloads) {
		http_response_code(429);
		exit('Te veel download aanvragen. Probeer het over 10 minuten opnieuw.');
	}
	
	// Record this attempt
	$_SESSION['basis_download_attempts'][] = time();
	
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
        
        // Regenerate CSRF token after successful submission to prevent resubmission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        $_SESSION['dataFeedbackMsg'] = "send message";
        include('pages/basis01.php');
}else{
    include('pages/basis01.php');
}
?>	
</div>