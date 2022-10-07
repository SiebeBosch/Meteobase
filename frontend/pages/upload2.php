<?php
$val = $_GET['val'];
$gebruiker = $_COOKIE['gebruiker'];
$i = 1;

// UPLOADEN //
if(isset($_FILES['files'])) {
    $errors = array();
	$extensie_array = array('shp','shx','dbf');
	
	$desired_dir = "uploads/".$gebruiker['sessionid']."_0001";
	
	if(is_dir($desired_dir)==false){
		mkdir("../".$desired_dir, 0700);
    }
	
	if(in_array(array('shp','shx','dbf'),$_FILES['files'])) {

	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name ){
		$file_name = $key.$_FILES['files']['name'][$key];
		$file_size = $_FILES['files']['size'][$key];
		$file_tmp = $_FILES['files']['tmp_name'][$key];
		$file_type = $_FILES['files']['type'][$key];	
        
		$extensie = substr(basename($file_name), strrpos(basename($file_name), '.') + 1);
		
		// CONTROLE BESTANDSGROOTTE
		if($file_size > 20971520){
			$errors[] = 'File size must be less than 20 MB';
        }
		
		if(empty($errors)==true){
		
            move_uploaded_file($file_tmp,"../$desired_dir/".$file_name);
       
        }else{
                print_r($errors);
        }
		
    }
	
	}
	
	if(empty($error)){
		echo "Success";
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MeteoBase uploader</title>
</head>

<body>

<form name="AddDL" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <p><input type="file" name="files[]" size="19" /> <br />Upload 01 *.shp bestand </a> </p>
	<p><input type="file" name="files[]" size="19" /> <br />Upload 02 *.shx bestand </a> </p>
	<p><input type="file" name="files[]" size="19" /> <br />Upload 03 *.dbf bestand </a> </p>
    <input type="hidden" name="submitform" />
    <p><input type="submit" value="uploaden" /></p>
</form>

</body>
</html>