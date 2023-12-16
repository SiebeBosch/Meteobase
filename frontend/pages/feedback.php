<?php
// Meteo Database
// Version 6-7
// April 2012
if(isset($_POST['userFullNameFeedback'])) {
	$naam = strip_tags($_POST['userFullNameFeedback']);
	$mail = strip_tags($_POST['userEmailFeedback']);
	$feedbackType = strip_tags($_POST['feedbackType']);
	$feedbackMessage = strip_tags($_POST['feedbackMessage']);
	$cmd = '"c:\Program Files\Hydroconsult\WIWBFEEDBACK\WIWBFEEDBACK.exe" "'. $naam . '" "' . $mail . '" "' . $feedbackType . '" "' . $feedbackMessage . '" ';
	$cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
	pclose(popen($cmd, 'r'));
	$_SESSION["feedbackMsg"]='Bedankt voor je bericht.';
	include('aanmeld01.php');
} else {
	$_SESSION['errorMsg'] = "Er is een fout opgetreden. Probeer het later opnieuw";
	include('aanmeld01.php');
}

?>
