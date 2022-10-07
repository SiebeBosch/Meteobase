<?php
$errors = array();
$var = false;
$extensions = array("shp","shx","dbf");
$gebruiker = $_COOKIE['gebruiker'];
$sessionid = $gebruiker['sessionid'];
$name = $sessionid.'_'.time();
$shapefile = array('shp' => 'mist','shx' => 'mist','dbf' => 'mist');

if(isset($_FILES['files'])){
	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name ){
		$file_size = $_FILES['files']['size'][$key];
		$file_tmp = $_FILES['files']['tmp_name'][$key];
		$file_type = $_FILES['files']['type'][$key];	
		
		if($file_size > 20971520){
			$errors[] = 'Het bestand ('.$file_size.') mag niet groter zijn dan 20MB.';
		}

		$file_ext = explode('.',$_FILES['files']['name'][$key]);
		$file_ext = end($file_ext);
		$file_ext = strtolower(end(explode('.',$_FILES['files']['name'][$key])));

		if(!in_array($file_ext,$extensions)) {
			$errors[] = "Het bestandstype komt niet overeen met een shapefile.";
		}else{
			$shapefile[$file_ext] = 'aanwezig';			
		}
		
		$desired_dir = "../uploads";

		if(empty($errors)) {
			if(is_dir($desired_dir) == false) {
				mkdir("$desired_dir", 0700);
			}
			
			$file_name = $name.'.'.$file_ext;
			move_uploaded_file($file_tmp,"$desired_dir/".$file_name);
			setcookie('gebruiker[files]',$naam);
		}else{
			break;
		}
	}

	foreach ($shapefile as $key => $value) {
		if($value == 'mist') {
			$errors[] = "Het bestandstype *.$key is niet geselecteerd.";
					if(file_exists("$desired_dir/$name.shp")) { unlink("$desired_dir/$name.shp"); }
					if(file_exists("$desired_dir/$name.shx")) { unlink("$desired_dir/$name.shx"); }
					if(file_exists("$desired_dir/$name.dbf")) { unlink("$desired_dir/$name.dbf"); }
		}
	}

	$var = true;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MeteoBase uploader</title>
</head>

<body>
<?php
if(empty($errors) && $var):
?>
<p>De bestanden zijn succesvol ge&uuml;pload.</p>
<button type="button" onclick="window.close()">sluiten</button>
<?php
elseif($var):
echo 'De volgende fouten zijn opgetreden:<br />';
foreach($errors as $value) {
	echo $value.'<br />';
}
?>
<p>Upload hier uw shapefile bestanden</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
	*.shp bestand: <input type="file" name="files[]" /><br />
    *.dbf bestand: <input type="file" name="files[]" /><br />
    *.shx bestand: <input type="file" name="files[]" /><br /><br />

	<input type="submit" value="uploaden" />
</form>
<?php
else:
?>
<p>Upload hier uw shapefile bestanden</p>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
	*.shp bestand: <input type="file" name="files[]" /><br />
    *.dbf bestand: <input type="file" name="files[]" /><br />
    *.shx bestand: <input type="file" name="files[]" /><br /><br />

	<input type="submit" value="uploaden" />
</form>
<?php
endif;
?>
</body>
</html>