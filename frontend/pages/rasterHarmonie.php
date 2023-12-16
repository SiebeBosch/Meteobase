<div id="gegevenspaneel_aanmelden">
<?php
// Meteo Database
// Version 6-7
// April 2012
$bericht = '';
$data = '';
$waarde_check = '';
if(isset($_POST['subbg'])){
    
    $datum_van = $_POST['intervalStartDate'];
    $harmonieTijd = $_POST['harmonieTijd'];
    $gegevenstype = $_POST['gegevenstype'];
	$fileName = $_POST['fileName'];
	$user = $_COOKIE['gebruiker'];
    $mail = $user['mail'];
    $naam = $user['naam'];
	$voorspellingshorizon = $_POST['voorspellingshorizon'];

    $cmd = '"c:/Program Files/Hydroconsult/VOORSPELLING/WIWBVOORSPELLING.exe" ' . $gegevenstype . ' ' . $datum_van . ' ' . $harmonieTijd . ' ' . $voorspellingshorizon . ' "' . $naam . '" '  . $mail . ' ' . $fileName . '.png';
	
    $cmd = 'start /B cmd /C "' . $cmd . '"';
	$_SESSION['rasterHarmonieImage'] = $fileName . '.png';
	$_SESSION['feedbackMsg'] = 'Uw bestelling is in goede orde ontvangen en wordt op dit moment verwerkt. U ontvangt een e-mail met download-link.';
	//var_dump($cmd);
    pclose(popen($cmd, 'r'));

	include('rasterHarmonieView.php');
}else{


    include('rasterHarmonieView.php');
}
	
?>	
	
</div>
