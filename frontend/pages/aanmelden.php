<?php
    include('backoffice/backoffice_onderhoud_variabel.php');
    if($onderhoud !== 'ja'){
        if(isset($_COOKIE['gebruiker'])){
            $gebruiker = $_COOKIE['gebruiker'];
            $naam = $gebruiker['naam'];
            $org = $gebruiker['org'];
            $mail = $gebruiker['mail'];
//            $tel = $gebruiker['tel'];
            include('aanmeld01.php');
        }else{
            include('aanmeld01.php');
        }
    }else{
        include('onderhoud.php');
    }
?>

