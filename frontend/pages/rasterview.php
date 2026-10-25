<div id="gegevenspaneel_aanmelden">
<?php
// Meteo Database
// Version 6-7
// April 2012
require_once 'sanitize.php';
$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subbg'])){
	if (!empty($_POST['website_url_hp'])) {
      
        exit(); 
    }
    //$waarde = $_POST['waarde'];
    $datum_van = $_POST['intervalStartDate'] ?? '';
    $datum_tot = $_POST['intervalEndDate'] ?? '';
	$fileName = $_POST['fileName'] ?? '';
	$user = valid_user_cookie();
	if ($user === null) {
		order_error(MSG_INVALID_USER, 'rasterview: gebruiker-cookie');
		include('rasterView01.php');
		return;
	}
	if (!preg_match('/^\d{8}$/', $datum_van) || !preg_match('/^\d{8}$/', $datum_tot)
		|| !preg_match('/^[A-Za-z0-9_-]{1,40}$/', $fileName)) {
		order_error(MSG_INVALID_INPUT, 'rasterview: datum/bestandsnaam');
		include('rasterView01.php');
		return;
	}
    $mail = $user['mail'];
    $naam = $user['naam'];

	if (!start_tool('WIWBHERHALINGSTIJD/WIWBHERHALINGSTIJD.exe', [$datum_van, $datum_tot, $naam, $mail, $fileName . '.png'])) {
		order_error(MSG_TOOL_FAILED, 'rasterview: WIWBHERHALINGSTIJD niet gestart');
		include('rasterView01.php');
		return;
	}
    $filePath = 'C:/Apache24/htdocs/images/rasterviews/' . $fileName . '.png';
    // wait for the image, at most 2 minutes
    $deadline = time() + 120;
    while(!is_file($filePath) && time() < $deadline) {
        sleep(1);
    }
	if (is_file($filePath)) {
		$_SESSION['rasterViewImage'] = $fileName . '.png';
		$_SESSION['feedbackMsg'] = 'Uw bestelling is in goede orde ontvangen en wordt op dit moment verwerkt. U ontvangt een e-mail met download-link.';
	} else {
		order_error('De kaart kon niet binnen 2 minuten worden gemaakt. U ontvangt een e-mail zodra de bestelling klaar is.', 'rasterview: geen afbeelding na 120 s');
	}
	include('rasterView01.php');
}else{
    include('rasterView01.php');
}
	
?>	
	
	
