<?php
// Voorbeeld van local_config.php. Kopieer naar local_config.php en vul de waarden in.
// local_config.php staat in .gitignore en mag NOOIT gecommit worden.

// Uitgaande mail (PHP mail())
ini_set('SMTP', 'mail.example.nl');
ini_set('smtp_port', 465);
ini_set('auth_username', 'gebruiker@example.nl');
ini_set('auth_password', '');
ini_set('sendmail_from', 'info@example.nl');
ini_set('sendmail_path', '');

// Database (webrol met alleen SELECT/INSERT op tbbasis en tbaanvrager en USAGE op de sequences)
if (!defined('MASTERUSER'))
	define('MASTERUSER', 'meteobase_web');
if (!defined('MASTERPASSWORD'))
	define('MASTERPASSWORD', '');
if (!defined('HOST'))
	define('HOST', 'localhost');
if (!defined('POORT'))
	define('POORT', '5432');
if (!defined('DATABASE'))
	define('DATABASE', 'postgis_meteobase');

// Google reCAPTCHA (aanmeld- en feedbackformulier)
if (!defined('RECAPTCHA_SITE_KEY'))
	define('RECAPTCHA_SITE_KEY', '');
if (!defined('RECAPTCHA_SECRET_KEY'))
	define('RECAPTCHA_SECRET_KEY', '');
?>
