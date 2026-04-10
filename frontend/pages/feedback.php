<?php
// Meteo Database
// Version 6-7
// April 2012
require 'sanitize.php';

if(isset($_POST['userFullNameFeedback'])) {
    if (!empty($_POST['website_url_hp'])) {

        $_SESSION["feedbackMsg"] = 'Bedankt voor je bericht.';
        include('aanmeld01.php');
        exit(); 
    }

    $min_submission_time = 3; 
    $form_start_time = isset($_POST['form_timestamp']) ? (int)$_POST['form_timestamp'] : 0;
    $time_taken = time() - $form_start_time;
    
    if ($form_start_time === 0 || $time_taken < $min_submission_time) {
        $_SESSION["feedbackMsg"] = 'Bedankt voor je bericht.';
        include('aanmeld01.php');
        exit();
    }
}

if(isset($_POST['userFullNameFeedback'])) {
	$naam = escapeshellarg($_POST['userFullNameFeedback']);
	$mail = escapeshellarg($_POST['userEmailFeedback']);
	$feedbackType = escapeshellarg($_POST['feedbackType']);
	$feedbackMessage = escapeshellarg($_POST['feedbackMessage']);

	$cmd = '"c:\Program Files\Hydroconsult\WIWBFEEDBACK\WIWBFEEDBACK.exe" ' . $naam . ' ' . $mail . ' ' . $feedbackType . ' ' . $feedbackMessage . ' ';
	$cmd = 'start /B cmd /C "' . $cmd . ' >NUL 2>NUL"';
    
	pclose(sanitezedPopen($cmd, 'r'));
    
	$_SESSION["feedbackMsg"]='Bedankt voor je bericht.';
	include('aanmeld01.php');
    
} else {
    
	$_SESSION['errorMsg'] = "Er is een fout opgetreden. Probeer het later opnieuw";
	include('aanmeld01.php');
    
}

?>  