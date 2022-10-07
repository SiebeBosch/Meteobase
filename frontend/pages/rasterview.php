<div id="gegevenspaneel_aanmelden">
<?php
// Meteo Database
// Version 6-7
// April 2012
$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subbg'])){
    
    //$waarde = $_POST['waarde'];
    $datum_van = $_POST['intervalStartDate'];
    $datum_tot = $_POST['intervalEndDate'];
	$fileName = $_POST['fileName'];
	$user = $_COOKIE['gebruiker'];
    $mail = $user['mail'];
    $naam = $user['naam'];
	
    $cmd = '"c:/Program Files/Hydroconsult/WIWBHERHALINGSTIJD/WIWBHERHALINGSTIJD.exe" ' . $datum_van . ' ' . $datum_tot . ' "' . $naam . '" '  . $mail . ' ' . $fileName . '.png';
	//echo($cmd);
	
    $cmd = 'start /B cmd /C "' . $cmd . '"';
	$_SESSION['rasterViewImage'] = $fileName . '.png';
	$_SESSION['feedbackMsg'] = 'Uw bestelling is in goede orde ontvangen en wordt op dit moment verwerkt. U ontvangt een e-mail met download-link.';
    pclose(popen($cmd, 'r'));

	sleep(2);

	include('rasterView01.php');
}else{


    include('rasterView01.php');
}
	
?>	
	
	
</div>