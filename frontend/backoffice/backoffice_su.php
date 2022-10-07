<?php
if(isset($_POST['submit_log'])){
	$log_nm = $_POST['log_naam'];
	$log_pass = $_POST['log_pass'];
	include('backoffice_onderhoud.php');
	if($log_nm == $inlog && $log_pass == $wachtw){
		include('backoffice_onderhoud_variabel.php');
		if($onderhoud == 'nee'){
			$script = '<?php $onderhoud=\'ja\'; ?>';
		}else{
			$script = '<?php $onderhoud=\'nee\'; ?>';
		}
		$pointer = fopen("backoffice_onderhoud_variabel.php","w");
		fputs($pointer,"$script");
		header("Refresh: 0; URL=../index.php");
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Onderhoud</title>
</head>

<body>

<?php
echo '<form action="/hc/backoffice/backoffice_su.php" method="post">';
echo '<input type="text" name="log_naam" value="inlog" /><br>';
echo '<input type="password" name="log_pass" value="123" /><br>';
echo '<input type="submit" name="submit_log" value="login">';
?>

</body>
</html>