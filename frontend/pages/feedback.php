<?php
// Meteo Database
// Version 6-7
// April 2012
require_once 'sanitize.php';

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
	$naam = valid_text($_POST['userFullNameFeedback'] ?? null, 100);
	$mail = valid_mail($_POST['userEmailFeedback'] ?? null);
	$feedbackType = $_POST['feedbackType'] ?? '';
	$feedbackMessage = valid_text($_POST['feedbackMessage'] ?? null, 2000, true, true);

	if ($naam !== null && $mail !== null && $feedbackMessage !== null
		&& in_array($feedbackType, ['vraag', 'bug', 'probleem', 'opmerking'], true)
		&& start_tool('WIWBFEEDBACK/WIWBFEEDBACK.exe', [$naam, $mail, $feedbackType, $feedbackMessage])) {
		$_SESSION["feedbackMsg"]='Bedankt voor je bericht.';
	} else {
		$_SESSION['errorMsg'] = 'Controleer uw naam, e-mailadres en bericht (max. 2000 tekens).';
	}
	include('aanmeld01.php');
    
} else {
    
	$_SESSION['errorMsg'] = "Er is een fout opgetreden. Probeer het later opnieuw";
	include('aanmeld01.php');
    
}

?>  