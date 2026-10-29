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
		$ms_go = TRUE;
		if(isset($_COOKIE['ms'])){
			$meteostations = $_COOKIE['ms'];
		}

		// Validate before the order is stored; the values go to WIWBBASIS (see sanitize.php)
		require_once 'sanitize.php';
		$user = valid_user_cookie();
		$stationsOk = isset($meteostations) && is_array($meteostations) && count($meteostations) > 0;
		foreach ($stationsOk ? $meteostations : [] as $stationNummer) {
			if (!is_string($stationNummer) || !preg_match('/^\d{1,6}$/', $stationNummer)) {
				$stationsOk = false;
			}
		}
		// Gegevenstype: alleen de waarden 'neerslag' en 'makkink' (whitelist). Zonder een van beide
		// kan backoffice_basisgegevens.php geen geldige INSERT maken en volgt een foutpagina.
		$neerslagGekozen = isset($_POST['neerslag']) && $_POST['neerslag'] === 'neerslag';
		$makkinkGekozen = isset($_POST['makkink']) && $_POST['makkink'] === 'makkink';
		$gegevenstypeGeldig = (!isset($_POST['neerslag']) || $neerslagGekozen) && (!isset($_POST['makkink']) || $makkinkGekozen);
		$geweigerd = true;
		if ($user === null) {
			order_error(MSG_INVALID_USER, 'basisgegevens: gebruiker-cookie');
		} elseif (!$gegevenstypeGeldig) {
			order_error(MSG_INVALID_INPUT, 'basisgegevens: invoer (ongeldig gegevenstype)');
		} elseif (!$neerslagGekozen && !$makkinkGekozen) {
			order_error('Uw bestelling is niet verwerkt: kies minstens één gegevenstype (Neerslag en/of Verdamping).', 'basisgegevens: invoer (geen gegevenstype)');
		} elseif (!in_array($waarde, ['Dagstations', 'Uurstations'], true) || !$stationsOk
			|| !preg_match('/^\d{8}$/', (string)$datum_van) || !preg_match('/^\d{8}$/', (string)$datum_tot)) {
			order_error('Uw bestelling is niet verwerkt: kies uurstations of dagstations, selecteer minstens 1 station op de kaart en controleer de datums.', 'basisgegevens: invoer');
		} else {
			$geweigerd = false;
		}
		if ($geweigerd) {
			$behoudKeuzes = true; // basis01.php vinkt de gekozen gegevenstypes weer aan
			include('pages/basis01.php');
			return;
		}
		$mail = $user['mail'];
		$naam = $user['naam'];
		$sessionid = $user['sessionid'];

		$basisGestart = false;
		include('backoffice_basisgegevens.php');
		if (!$basisGestart) {
			order_error(MSG_TOOL_FAILED, 'basisgegevens: WIWBBASIS niet gestart');
			include('pages/basis01.php');
			return;
		}

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