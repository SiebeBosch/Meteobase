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
    
    $datum_van = $_POST['intervalStartDate'] ?? '';
    $harmonieTijd = $_POST['harmonieTijd'] ?? '';
    $gegevenstype = $_POST['gegevenstype'] ?? '';
	$fileName = $_POST['fileName'] ?? '';
	$voorspellingshorizon = $_POST['voorspellingshorizon'] ?? '';
	$user = valid_user_cookie();
	if ($user === null) {
		order_error(MSG_INVALID_USER, 'rasterHarmonie: gebruiker-cookie');
	} elseif (!preg_match('/^\d{8}$/', $datum_van) || !in_array($gegevenstype, ['HIRLAM', 'HARMONIE'], true)
		|| !in_array($harmonieTijd, ['0', '6', '12', '18'], true) || !in_array($voorspellingshorizon, ['6', '18', '48'], true)
		|| !preg_match('/^[A-Za-z0-9_-]{1,40}$/', $fileName)) {
		order_error(MSG_INVALID_INPUT, 'rasterHarmonie: invoer');
	} elseif (!start_tool('VOORSPELLING/WIWBVOORSPELLING.exe', [$gegevenstype, $datum_van, $harmonieTijd, $voorspellingshorizon, $user['naam'], $user['mail'], $fileName . '.png'])) {
		order_error(MSG_TOOL_FAILED, 'rasterHarmonie: WIWBVOORSPELLING niet gestart');
	} else {
		$_SESSION['rasterHarmonieImage'] = $fileName . '.png';
		$_SESSION['feedbackMsg'] = 'Uw bestelling is in goede orde ontvangen en wordt op dit moment verwerkt. U ontvangt een e-mail met download-link.';
	}

	include('rasterHarmonieView.php');
}else{


    include('rasterHarmonieView.php');
}
	
?>	
	
</div>
