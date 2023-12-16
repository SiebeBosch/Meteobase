<?php
	$valid_file = true;
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = strtolower($_FILES['file']['name']);
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/meteobase/uploads/";
    if($_FILES['sile']['size'] > (20971520)) { $valid_file = false; }
    $file_ext = end(explode('.',$file_name));
    if($file_ext !== 'zip') { $valid_file = false; }
    if($valid_file) { move_uploaded_file($file_tmp,"$upload_dir".$file_name); }
    $locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\\' . $file_name;
    setcookie('ZIPFILE', $locatie_zip, time() + (86400 * 30), "/");
	echo $file_name;
?>