<?php
// Meteo Database
// Version 8
// march 2017
include('local_config.php');

session_start();
//IE browser detector
if (isset($_SERVER['HTTP_REFERER'])){
	$redirect = strpos($_SERVER['HTTP_REFERER'], 'meteobase.nl')?true:false;
}
if (isset($_SERVER["HTTP_USER_AGENT"])) {
	$browser_ie = strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE')?true:false;
}

if($browser_ie && $redirect) {
	echo '<script>window.open("https://www.meteobase.nl:8080/meteobase/","_blank");</script>';
	exit;
}

// URL
if(isset($_GET['tb'])){
	$tab = $_GET['tb'];
	$url = '?tb=' . $tab . '&';
	$urlsub = '?tb=' . $tab;
}else{
	$tab = 'aanmelden';
	$url = '?tb=' . $tab . '&';
}


// Onderhoud
if($tab == 'onderhoud'){
	include('backoffice/backoffice_su.php');
	exit;
}

function rd2wgs ($x, $y){
    // Calculate WGS84 coordinates
    $dX = ($x - 155000) * pow(10, - 5);
    $dY = ($y - 463000) * pow(10, - 5);
    $SomN = (3235.65389 * $dY) + (- 32.58297 * pow($dX, 2)) + (- 0.2475 * pow($dY, 2)) + (- 0.84978 * pow($dX, 2) * $dY) + (- 0.0655 * pow($dY, 3)) + (- 0.01709 * pow($dX, 2) * pow($dY, 2)) + (- 0.00738 * $dX) + (0.0053 * pow($dX, 4)) + (- 0.00039 * pow($dX, 2) * pow($dY, 3)) + (0.00033 * pow($dX, 4) * $dY) + (- 0.00012 * $dX * $dY);
    $SomE = (5260.52916 * $dX) + (105.94684 * $dX * $dY) + (2.45656 * $dX * pow($dY, 2)) + (- 0.81885 * pow($dX, 3)) + (0.05594 * $dX * pow($dY, 3)) + (- 0.05607 * pow($dX, 3) * $dY) + (0.01199 * $dY) + (-0.00256 * pow($dX, 3) * pow($dY, 2)) + (0.00128 * $dX * pow($dY, 4)) + (0.00022 * pow($dY, 2)) + (- 0.00022 * pow($dX, 2)) + (0.00026 * pow($dX, 5));
    $Latitude = 52.15517 + ($SomN / 3600);
    $Longitude = 5.387206 + ($SomE/ 3600);
    return array(
        'latitude' =>        $Latitude ,
        'longitude' =>        $Longitude
	);
}

// Kaartenpaneel
$initkaart = array('aanmelden' => 'stations_blocked', 'basisgegevens' => 'stations', 'rasterdata' => 'raster', 'toetsingsdata' => 'polygonen');

// Koppeling kaartenpaneel - gegevenspaneel
if(isset($_COOKIE['crdphp'])){
	$coord = $_COOKIE['crdphp'];
	//$coord1 = array("(", ")", " ");
	//$coord2 = str_replace($coord1, "", $coord);
	$coord3 = explode(",", $coord);
	foreach($coord3 as $key => $val) {
		$coord3[$key] = round($val, 5);
		setcookie("coordinaat[" . $key . "]", $coord3[$key]);
	}
	// Conversie naar latlon coordinaten
	//$southwest = rd2wgs($coord3[0],$coord3[1]);
	//$northeast = rd2wgs($coord3[2],$coord3[3]);
	$coord_0 = $coord3[0];
	$coord_1 = $coord3[1];
	$coord_2 = $coord3[2];
	$coord_3 = $coord3[3];
}else{
// Default coordinaten
	// Latlon coords
	//$coord_0 = '50.65001';
	//$coord_1 = '3.06582';
	//$coord_2 = '53.55560';
	//$coord_3 = '7.36133';
	
	//RD coords
	$coord_0 = '-9173';
	$coord_1 = '298146';
	$coord_2 = '285825';
	$coord_3 = '620635';
}

// Gegevenspaneel - Menu
$menu = array('aanmelden' => 'menu','basisgegevens' => 'menu','rasterdata' => 'menu','toetsingsdata' => 'menu','neerslagreductie' => 'menu','rasterdataview' => 'menu','colofon' => 'menu', 'feedback' => 'menu');
$menu[$tab] = 'main';
if(!isset($_COOKIE['gebruiker'])){
	$link01 = array('aanmelden' => '','basisgegevens' => '','rasterdata' => '','toetsingsdata' => '', 'feedback' => '');
	$link02 = array('aanmelden' => '','basisgegevens' => '','rasterdata' => '','toetsingsdata' => '', 'feedback' => '');
}else{
	$link01 = array('aanmelden' => '<a href="?tb=aanmelden&dp=aanmelden">','basisgegevens' => '?tb=basisgegevens&dp=basisgegevens','rasterdata' => '?tb=rasterdata&dp=rasterdata&dp_sub=introductie','toetsingsdata' => '?tb=toetsingsdata&dp=toetsingsdata', 'feedback' => '<a href="?tb=feedback&dp=colofon">');
	$link02 = array('aanmelden' => '</a>','basisgegevens' => '</a>','rasterdata' => '</a>','toetsingsdata' => '</a>', 'feedback' => '</a>');
	$link01[$tab] = '';
	$link02[$tab] = '';
}

//Gegevenspaneel - Content
$content = 'pages/' . $tab . '.php';
$bericht = '';

// Aanmeld script
if(isset($_POST['userFullName'])) {
	if(isset($_POST['userFullName']) && $_POST['userFullName'] != ''){
		$naam = $_POST['userFullName'];
	}else{
		$naam_ontbreekt = TRUE;
	}
	if(isset($_POST['userCompany']) && $_POST['userCompany'] != ''){
		$org = $_POST['userCompany'];
	}else{
		$org_ontbreekt = TRUE;
	}
	if(isset($_POST['userEmail']) && $_POST['userEmail'] != ''){
		$mail = $_POST['userEmail'];
		$mail_check = explode("@", $mail);
		$mail_count = count($mail_check);
		if($mail_count != '1'){
			$mail = urldecode($_POST['userEmail']);
			$mail_checked = TRUE;
		}else{
			$mail_ontbreekt = TRUE;
		}
	}else{
		$mail_ontbreekt = TRUE;
	}
	
	if(isset($_POST['tel'])){
		$tel = trim($_POST['tel']);
		if($tel == '' or strlen($tel) > 14){
			$tel = ' ';
		}
	}
	if(!isset($naam) or !isset($org) or !isset($mail_checked)){
		$_SESSION["errorMsg"]='Graag alle velden invullen.';
	}else{
		include('backoffice/ScriptLoginApplication.php');
		setcookie('gebruiker[naam]',$naam);
		setcookie('gebruiker[org]',$org);
		setcookie('gebruiker[mail]',$mail);
		$_SESSION['feedbackMsg'] = 'Je bent succesvol geregistreerd!';
		header("Refresh: 0; URL=index.php");
	}
}

// Documentatiepaneel
if(isset($_GET['dp'])){
	$dp = $_GET['dp'];
	$urlsub = $url . 'dp=' . $dp;
}else{
	$dp = 'introductie';
}
if(isset($_GET['dp_sub'])){
	$dp_sub = $_GET['dp_sub'];
	$urlsub = $url . 'dp=' . $dp . '&dp_sub=' . $dp_sub . '&';
}

$adres = 'https://www.meteobase.nl:8080/meteobase/downloads/fixed/Regios.zip';
$adresARF = 'https://www.meteobase.nl:8080/meteobase/downloads/fixed/Neerslagreductie.zip';
$adresRapp = 'https://www.meteobase.nl:8080/meteobase/downloads/fixed/Rapport_Meteobase_definitief.pdf';
$adresRapp2 = 'https://www.meteobase.nl:8080/meteobase/downloads/fixed/STOWA_2015_10_webversie_LR2.pdf';
$adresVerslagDec2015 = 'https://www.meteobase.nl:8080/meteobase/downloads/fixed/Verslag_17_december_2015.zip';
$adresHKV = 'https://www.hkv.nl';
$adresHC = 'https://www.hydroconsult.nl';
$adresGP = 'https://www.geopro.nl';
$adresSTOWA = 'https://www.stowa.nl';
$link = "<a href='$adres'>hier</a>";
$linkARF = "<a href='$adresARF'>Neerslagreductie</a>";
$linkRapp = "<a href='$adresRapp'>hier</a>";
$linkRapp2 = "<a href='$adresRapp2'>rapport</a>";
$linkVerslagDec2015 = "<a href='$adresVerslagDec2015'>hier</a>";
$linkHKV = "<a href='$adresHKV'>HKV - lijn in water</a>";
$linkHC = "<a href='$adresHC'>Hydroconsult</a>";
$linkGP = "<a href='$adresGP'>Geopro</a>";
$linkSTOWA = "<a href='$adresSTOWA'>STOWA</a>";
$docu_menu = array(
'introductie' => '<p>Welkom bij Meteobase.nl: een online service van ' .$linkSTOWA . '. Vanaf deze website kunt u historische neerslag- en verdampingsgegevens voor heel Nederland downloaden.</p> 
				  <p>Het achtergrondrapport voor informatie over basisgegevens en rasterdata vindt u ' . $linkRapp . '. Dit rapport bevat ook nog informatie over de toetsingsdata die tot medio oktober 2015 op Meteobase aangeboden werden. Per medio oktober 2015 zijn deze data geactualiseerd. Uitgebreide toelichting op deze nieuwe data vindt u in dit ' . $linkRapp2 .'.</p>
                  <p> In een bijeenkomst op 17 december 2015 is dit laatste onderzoek toegelicht, het verslag en de presentaties vindt u ' . $linkVerslagDec2015 .'.</p>
				  <p>Deze online dienst is in het leven geroepen om medewerkers van waterschappen en adviesbureaus in de watersector te ondersteunen bij het uitvoeren van modelstudies waarvoor meteorologische gegevens nodig zijn.</p>
				  <p>Let op: deze website maakt gebruik van cookies. Klik <a href="https://www.meteobase.nl:8080/meteobase/index.php?tb=aanmelden&dp=aanmelden">hier</a> voor meer informatie.</p>',
//				  <p>Nadat u zich heeft aangemeld, kunt u bestellingen plaatsen op de volgende tabbladen:</p>
//				  <p>- Basisgegevens (neerslag en verdamping van KNMI-meetstations)
//				  <br>- Rasterdata (geijkte neerslagradargegevens)
//				  <br>- Toetsingsdata (langjarige homogene tijdreeksen en statistiek).</p>

'aanmelden' => '<p>Om data van deze website te kunnen betrekken, dient u allereerst uw naam, bedrijfsnaam, e-mailadres en telefoonnummer op te geven. Na het aanmelden krijgt u automatisch toegang tot de tabbladen Basisgegevens, Rasterdata en Toetsingsdata.</p><p>Nadat u uw bestelling hebt geplaatst, krijgt u uw sessie-ID te zien en een link naar de download-directory. Afhankelijk van de omvang van uw bestelling kan het enige tijd duren eer uw gegevens online staan.</p>
<p>Meteobase.nl maakt bij het aanbieden van haar diensten gebruik van cookies. Dit zijn bijvoorbeeld cookies die gebruikt worden om u, nadat u ingelogd bent, te kunnen blijven herkennen. Naast deze functionele cookies worden tevens cookies gebruikt voor het bijhouden van websitestatistieken.</p><p>Op de site van de overheidsinstantie <a href="https://www.waarschuwingsdienst.nl/Risicos/Inbreuk+op+je+privacy/Cookies-+een+bedreiging+voor+uw+privacy.html">Waarschuwingsdienst.nl</a> vindt u meer informatie over cookies.</p>',
'basisgegevens' => '<p>Dit tabblad bevat de basisgegevens zoals geproduceerd door het KNMI. Maak eerst de keuze voor uurstations of dagstations, en selecteer daarna op de kaart de stations van welke u gegevens wilt downloaden. Merk op dat verdampingscijfers alleen beschikbaar zijn op de uurstations.</p>
                <p>Wanneer u de cursor van uw muis boven een station beweegt, verschijnt de naam van het desbetreffende station.</p>
				 <p>Wilt u de gedownloade neerslaggegevens toepassen op een groot gebiedsoppervlak? Bereken dan de benodigde neerslagreductie met de Excel-macro ' . $linkARF . '</p>
				<p>DEZE GEGEVENS MOGEN VRIJELIJK WORDEN GEBRUIKT MITS DE VOLGENDE BRONVERMELDING WORDT GEGEVEN: KONINKLIJK NEDERLANDS METEOROLOGISCH INSTITUUT (KNMI)<p>',
'rasterdata' => array(
	'introductie' => "<p>Grids met neerslag en verdamping.</p><p>Veel (geo)hydrologische simultatieprogramma's vragen om meteorologische data in rasterformaat. Andere modellen vragen juist om gebiedsgemiddelde neerslagvolumes die zijn afgeleid uit de ruimtelijk verdeelde neerslag op een gebied.</p>
	                  <p>Om aan dergelijke wensen van hydrologen tegemoet te komen, biedt deze sectie u de mogelijkheid om neerslag en verdamping voor een vrij te kiezen deelregio te downloaden in rasterformaat.</p><p>De neerslagvolumes bestaan uit radargegevens die aan meetwaarden van 216 grondstations zijn geijkt, en kunnen daarom worden gebruikt voor kalibratiedoeleinden.</p>",
	'algoritmes' => '<p>Informatie over de algoritmes.</p>
	                <p>Als u geinteresseerd bent in de wijze waarop de ruwe radargegevens werden geijkt aan de meetwaarden van de grondstations, nodigen wij u van harte uit om ' . $linkRapp . ' het rapport van HKV - lijn in water te downloaden.</p>'
	),
'toetsingsdata' => '<p>In deze sectie kunt u meteorologische gegevens downloaden ten behoeve van statistische analyses zoals hoogwaterstudies.</p>
                   <p>Omdat KNMI-station De Bilt beschikt over de langste homogene dataset van Nederland (1906-heden), zijn alle gegevens in deze sectie ontleend aan meetwaarden van dit station.</p>
                   <p>Station De Bilt kan echter niet representatief worden geacht voor heel Nederland. Daarom heeft KNMI een regioverdeling gepubliceerd die bestaat uit vier klassen: Mild, De Bilt, Hevig en Zeer hevig. Voor elk van deze klassen is een eigen langjarige tijdreeks en stochastiek afgeleid.</p>
				   <p>Download deze regioverdeling in shape-formaat '  . $link . '.</p>
    			   <p>Wilt u de gedownloade neerslaggegevens toepassen op een groot gebiedsoppervlak? Bereken dan de benodigde neerslagreductie met de Excel-macro ' . $linkARF . '</p>',
'neerslagreductie' => '<p>Als u de gedownloade puntneerslagreeksen wilt toepassen op een groot oppervlak, moeten de hoge neerslagvolumes worden gereduceerd.</p>
                   <p>Download hiertoe de Excel-macro "Neerslagreductie" ' . $linkARF . '</p>
                   <p>Station De Bilt kan echter niet representatief worden geacht voor heel Nederland. Daarom heeft KNMI een regioverdeling gepubliceerd die bestaat uit vier klassen: Mild, De Bilt, Hevig en Zeer hevig. Voor elk van deze klassen is een eigen langjarige tijdreeks en stochastiek afgeleid.</p>
				   <p>Download deze regioverdeling in shape-formaat '  . $link . '.</p>
				   <p>Wilt u de gedownloade neerslaggegevens toepassen op een groot gebiedsoppervlak? Bereken dan de benodigde neerslagreductie met de Excel-macro ' . $linkARF . '</p>',
'colofon' => '<p>Deze online service werd ontwikkeld in opdracht van ' . $linkSTOWA . '.</p>
             <p>Alle meteorologische gegevens werden ontsloten en bewerkt door ' . $linkHKV . '. Het bijbehorende rapport kunt u '  . $linkRapp . ' downloaden.</p>
			 <p>De ontwikkeling van de website, database en online hosting was in handen van ' . $linkHC . ' met medewerking van ' . $linkGP . '.<p>
			 <p>Supportvragen kunnen worden gericht aan het algemene e-mailadres van meteobase: info_apenstaartje_meteobase.nl.</p>
			 <p>Aan de teksten en gegevens op deze website heeft STOWA veel zorg en aandacht besteed om de juistheid en actualiteit ervan te waarborgen. Het kan desondanks voorkomen dat er fouten in zijn geslopen. Mocht u informatie tegenkomen die in uw ogen niet correct (meer) is, of verouderd, laat het ons weten. STOWA aanvaardt geen aansprakelijkheid voor deze fouten.</p>'
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" >
<head>
<link REL="SHORTCUT ICON" HREF="https://www.meteobase.nl/images/favicon.ico">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<script type="text/javascript" src="maps/stations.js"></script>
<script type="text/javascript" src="maps/polygonen.js"></script>

<script
  src="https://code.jquery.com/jquery-1.11.0.js"
  integrity="sha256-zgND4db0iXaO7v4CLBIYHGoIIudWI5hRMQrPB20j0Qw="
  crossorigin="anonymous"></script>
<script type="text/javascript" src="js/map-toggle.js"></script>

<title>Historisch verloop en statistiek van neerslag en verdamping in Nederland</title>
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Lato|Open+Sans" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	
	<![endif]-->
<script type="text/javascript">
function melding() { document.getElementById('dl').innerHTML='<p>Uw bestelling wordt verwerkt. U ontvangt binnen een enkele uren een e-mail<br />met een downloadlink. U mag dit tabblad verlaten.</p>'; $('#downloadbutton').fadeOut('fast'); }
function check() {
    var wDag = document.getElementById('waarde_dag').checked;
    var wUur = document.getElementById('waarde_uur').checked;
    if(wDag) { document.getElementById('waarde_dag').click(); }
    if(wUur) { document.getElementById('waarde_uur').click(); }
}
function test(val) {
    if(val == 'disable') {
        document.getElementById('makkink').checked=false;
        document.getElementById('makkink').disabled=true;
    }
    if(val == 'enable') {
        document.getElementById('makkink').disabled=false;
    }
}
window.onload = function() {
//    eraseStations;
//    initialize();
<?php //if($initkaart[$tab] == 'stations') { echo '    check();' . "\n"; } ?>
}
</script>
</head>

<body>
<!--Begin Layout-->
<div class="layout">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<!--Starting Map-->
					<div class="col-md-6 col-sm-12 col-xs-12 map-container pull-right">
                        <?php  if ($_GET['tb'] == 'toetsingsdata' && $_GET['dp'] == 'toetsingsdata') { ?>
                             <div class="row">
                                <div id="toetsingsdata-toggle">
                                    <input type="radio" name="toetsingsdata-select" checked id="toetsingsdata-year-select"> Hele jaar<br />
                                    <input type="radio" name="toetsingsdata-select" id="toetsingsdata-winter-select"> Winterseizoen
                                </div>
                                <img src="images/map/KAM_IsoPrmY.png" alt="Year" id="toetsingsdata-map-year"/>
                                <img src="images/map/KAM_IsoPrmW.png" alt="Winter" id="toetsingsdata-map-winter"/>
                            </div>
                        <?php  } else { ?>
							<div class="row">
								<a class="skiplink" href="#map">Go to map</a>
								<div id="map" class="map" tabindex="0"></div>
									<div id="legend">
										<div class="title">
										<h4>Herhalingstijd</h4>
										<p>jaren</p>
										</div>
										<ul class="legend-items">
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(50, 136, 189);"></div><span class="item">&lt; 5</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(102, 194, 165);"></div><span class="item">5-10</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(171, 221, 164);"></div><span class="item">10-30</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(230, 245, 152);"></div><span class="item">30-50</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(255, 255, 191);"></div><span class="item">50-70</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(254, 224, 139);"></div><span class="item">70-90</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(253, 174, 97);"></div><span class="item">90-100</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(244, 109, 67);"></div><span class="item">100-500</span>
											</li>
											<li class="legend-item">
												<div class="colour" style="background-color: rgb(213, 62, 79);"></div><span class="item">&gt; 500</span>
											</li>
										</ul>
									</div>
							<button id="zoom-out">Zoom out</button>
							<button id="zoom-in">Zoom in</button>
						</div>
						<?php  } ?>
					</div>
					<!--Ending Map-->
					<!--Starting Main Content-->
					<div class="col-md-6 col-sm-12 col-xs-12 main-content pull-left">
						<!--Starting Header-->
						<div class="row">
							<header class="head-container" data-stellar-background-ratio="0.5"
									data-stellar-horizontal-offset="50">
								<div class="overlay"></div>
								<!--Starting Top Bar-->
								<div class="top-bar col-md-12 col-sm-12 col-xs-12">
									<div class="logo">
										<a class="logo-link" href="https://www.meteobase.nl">Meteobase</a>
									</div>
									
									<div class="registration-link">
										<?php
										if (!isset($_COOKIE['gebruiker'])) {
											echo '<a href="?tb=aanmelden&dp=aanmelden" class="modal-opener" data-toggle="modal" data-target="#registerModal">Registreren</a>';
										} else {
											echo '<a href="?tb=aanmelden&dp=aanmelden" class="modal-opener" data-toggle="modal" data-target="#registerModal"><i class="glyphicon glyphicon-user"></i>' . $_COOKIE["gebruiker"]["naam"] . '<i class="glyphicon glyphicon-pencil"></i></a>';
										}
										?>
									</div>
									<!-- Toggle for better mobile display -->
									<div class="mobile-menu-toggle">
										<i class="glyphicon glyphicon-menu-hamburger"></i>
									</div>
									<!--End of mobile menu toggle-->
									<!--Mobile menu start-->
									<ul class="mobile-menu">
										<li><a href="?tb=basisgegevens&dp=basisgegevens">Basisgegevens</a></li>
										<li><a href="?tb=rasterdata&dp=rasterdata&dp_sub=introductie">Downloaden</a></li>
										<li><a href="?tb=rasterview&dp=rasterview&dp_sub=introductie">Herhalingstijd</a></li>
										<!--<li><a href="?tb=rasterHarmonie&dp=rasterHarmonie&dp_sub=introductie">Hirlam/Harmonie</a></li>-->
										<li><a href="?tb=toetsingsdata&dp=toetsingsdata">Statistiek</a></li>
										<li><a href="#" class="modal-opener" data-toggle="modal" data-target="#feedbackModal">Feedback</a></li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#introductionModal">
												Introductie
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#registrationModal">
												Registreren
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#basicInfoModal">
												Basisgegevens
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#rasterDataModal">
												Rasterdata
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#testingDataModal">
												Statistiek
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#colophonModal">
												Colofon
											</a>
										</li>
										<li>
											<a href="#"
											   class="modal-opener"
											   data-toggle="modal"
											   data-target="#literatureModal">
												Literatuur
											</a>
										</li>
										
									</ul>
									<!--Mobile Menu End-->
								</div>
								<!--Ending Top Bar-->
								<!--Starting heading-->
								<h1 class="main-heading">
									Het online archief van historische neerslag en verdamping in Nederland
									<!--font color="red">METEOBASE IS TIJDELIJK OFFLINE WEGENS GROOT ONDERHOUD</font-->
								</h1>
								<!--Ending Header-->
							</header>
						</div>
						<!--Ending Header-->
						<div class="row">
							<!--Starting Menu-->
							<nav class="navbar navbar-default hidden-xs hidden-sm">
								<!-- Collect the nav links, forms, and other content for toggling -->
								<div class="row">
									<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
										<ul class="nav navbar-nav">
											<li class="registration"><a class="modal-opener" href="#" data-toggle="modal" data-target="#registerModal">Registreren</a></li>
											<li><a href="?tb=basisgegevens&dp=basisgegevens">Basisgegevens</a></li>
											
											<li class="dropdown">
												<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Rasterdata <span class="caret"></span></a>
												<ul class="dropdown-menu">
													<li><a href="?tb=rasterdata&dp=rasterdata&dp_sub=introductie">Downloaden</a></li>
													<li><a href="?tb=rasterview&dp=rasterview&dp_sub=introductie">Herhalingstijd</a></li>
													<li><a href="?tb=satdata&dp=satdata&dp_sub=introductie">SAT Data 2.0</a></li>
													<!--<li><a href="?tb=rasterHarmonie&dp=rasterHarmonie&dp_sub=introductie">Hirlam/Harmonie</a></li>-->
												</ul>
											</li>
											<li class="dropdown">
												<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" href="?tb=toetsingsdata&dp=toetsingsdata">Statistiek <span class="caret"></span></a>
												<ul class="dropdown-menu">
													<li>
														<a href="?tb=toetsingsdata&dp=toetsingsdata">Toetsingsdata</a>
													</li>
													<li>
														<a href="#" class="modal-opener" data-toggle="modal" data-target="#chartDialogStochasten">Regenduurlijnen 2019</a>
													</li>
													<li>
														<a href="#" class="modal-opener" data-toggle="modal" data-target="#chartDialogOppervlaktereductie">Oppervlaktereductie</a>
													</li>
													<li>
														<a href="?tb=stedelijk&dp=toetsingsdata">Neerslaggebeurtenissen stedelijk</a>
													</li>
												</ul>
											</li>
											<li><a href="#" class="modal-opener" data-toggle="modal" data-target="#feedbackModal">Feedback</a></li>
											<li class="dropdown">
												<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Documentatie <span class="caret"></span></a>
												<ul class="dropdown-menu">
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#introductionModal">
															Introductie
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#registrationModal">
															Registreren
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#basicInfoModal">
															Basisgegevens
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#rasterDataModal">
															Rasterdata
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#testingDataModal">
															Toetsingsdata
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#colophonModal">
															Colofon
														</a>
													</li>
													<li>
														<a href="#"
														   class="modal-opener"
														   data-toggle="modal"
														   data-target="#literatureModal">
															Literatuur
														</a>
													</li>
												</ul>
											</li>
										</ul>
									</div><!-- /.row -->
								</div><!-- /.navbar-collapse -->
							</nav>
							<!--Ending Menu-->
						</div>
						<!--Starting Content-->
						<?php include($content); ?>
						
						<!--Starting Partners Section-->
						<div class="row">
							<div class="partners content-row">
								<h2 class="section-title">
									Onze Partners
								</h2>
								<div class="col-md-12">
									<div class="col-md-3 col-sm-12 col-xs-12 partner-logo">
										<a href="https://www.hetwaterschapshuis.nl" target="_blank"><img src="images/hetwaterschaps.png" class="img-responsive" alt="Het Waterschapshuis"></a>
									</div>
									<div class="col-md-3 col-sm-12 col-xs-12 partner-logo">
										<a href="https://www.stowa.nl" target="_blank"><img src="images/stowa.png" class="img-responsive" alt="STOWA"></a>
									</div>
									<div class="col-md-3 col-sm-12 col-xs-12 partner-logo">
										<a href="https://www.hydroconsult.nl" target="_blank"><img src="images/logo_hc.png" class="img-responsive" alt="Hydroconsult.nl"></a>
									</div>
									<div class="col-md-3 col-sm-12 col-xs-12 partner-logo">
										<a href="https://www.hydrologic.com" target="_blank"><img src="images/hydrologic.png" class="img-responsive" alt="hydrologic"></a>
									</div>
									<div class="col-md-3 col-sm-12 col-xs-12 partner-logo">
										<a href="https://www.hkv.nl" target="_blank"><img src="images/logo_hkv.png" class="img-responsive" alt="HKV"></a>
									</div>
								</div>
							</div>
						</div>
						<!--Ending Partners Section-->
						<!--Ending Content-->
						<!--Starting Footer-->
						<div class="row">
							<footer class="footer col-md-12 col-sm-12 col-xs-12">
								<div class="copyright">
									© 2016 Meteobase.nl
								</div>
								<div class="prodevel pull-right">
									<a href="https://prodevel.solutions" target="_blank">
										<img src="images/logo_prodevel.png" alt="Prodevel.solutions">
									</a>
								</div>
							</footer>
						</div>
						<!--Ending Footer-->
					</div>
					<!--Ending Of Main Content-->
				</div>
			</div>
		</div>
	</div>
</div>
<!--End of Meteobase Layout-->

<!--Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="registerModalLabel">Registreren</h4>
			</div>
			<div class="modal-body">
				<form method="POST" id="registerModalForm" action="<?php echo($_SERVER["PHP_SELF"]); ?>" class="register-modal">
					<div class="form-group col-md-12 col-sm-12 col-xs-12">
						<label for="userFullName">Naam</label>
						<div class="input-group">
							<span class="input-group-addon" id="name"><i class="fa fa-user"></i></span>
							<input type="text" name="userFullName" id="userFullName" class="form-control" value="<?php if (isset($_COOKIE['gebruiker'])) { echo $_COOKIE['gebruiker']['naam'];} else { echo '';} ?>"  placeholder="Full Name" aria-describedby="name">
						</div>
					</div>
					<div class="form-group col-md-12 col-md-12">
						<label for="userCompany">Bedrijf</label>
						<div class="input-group">
							<span class="input-group-addon" id="company"><i class="glyphicon glyphicon-briefcase"></i></span>
							<input type="text" id="userCompany" name="userCompany" class="form-control" value="<?php if (isset($_COOKIE['gebruiker'])) { echo $_COOKIE['gebruiker']['org'];} else { echo '';} ?>" placeholder="Company" aria-describedby="company">
						</div>
					</div>
					<div class="form-group col-md-12 col-md-12">
						<label for="userEmail">E-mail</label>
						<div class="input-group">
							<span class="input-group-addon" id="email"><i class="fa fa-envelope-o"></i></span>
							<input type="text" id="userEmail" name="userEmail" class="form-control" placeholder="Email" value="<?php if (isset($_COOKIE['gebruiker'])) { echo $_COOKIE['gebruiker']['mail'];} else { echo '';} ?>" aria-describedby="email">
						</div>
					</div>				
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
				<button type="button" class="btn submit-btn btn-primary"><?php if (isset($_COOKIE['gebruiker'])) { echo 'Bijwerken';} else { echo 'Registreren';} ?></button>
			</div>
		</div>
	</div>
</div>
<!--End of Register Modal-->
<!--Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="feedbackModalLabel">Dit feedback-formulier is om veiligheidsredenen onklaar gemaakt en zal op termijn worden herzien. Neem bij vragen contact op met Meteobase op het adres info-apenstaartje-meteobase-punt-nl</h4>
			</div>
			<div class="modal-body">
				<form method="POST" id="feedbackModalForm" action="index.php?tb=feedback" class="register-modal">
					<div class="form-group col-md-6 col-sm-12 col-xs-12">
						<label for="userFullNameFeedback">Naam</label>
						<input type="text" name="userFullNameFeedback" id="userFullNameFeedback" class="form-control" placeholder="Name" aria-describedby="name">
					</div>
					<div class="form-group col-md-6 col-sm-12 col-xs-12">
						<label for="userEmailFeedback">E-mail</label>
						<input type="text" id="userEmailFeedback" name="userEmailFeedback" class="form-control" placeholder="Email" aria-describedby="email">
					</div>
					
					<div class="form-group  col-md-12 col-sm-12 col-xs-12">
						<label for="feedbackType"> U heeft een:</label>
						<select id="feedbackType" name="feedbackType" class="form-control">
							<option selected value="vraag">Vraag</option>
							<option value="bug">Bug</option>
							<option value="probleem">Probleem</option>
							<option value="opmerking">Opmerking</option>
						</select>
					</div>
					<div class="form-group col-md-12 col-sm-12 col-xs-12">
						<label for="feedbackMessage">Vul hieronder uw vraag, bug, probleem of opmerking in:</label>
						<textarea id="feedbackMessage" name="feedbackMessage" class="form-control" rows="3">
						</textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<!--button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
				<button type="button" class="btn submit-btn btn-primary">Stuur bericht</button-->
			</div>
		</div>
	</div>
</div>
<!--End of Feedback Modal-->
<!--Introduction Modal -->
<div class="modal fade" id="introductionModal" tabindex="-1" role="dialog" aria-labelledby="introductionModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="introductionModalTitle">Introductie</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12">
					<p>
						Welkom bij Meteobase.nl: een online service van <a
						href="https://www.hetwaterschapshuis.nl" target="_blank">Het Waterschapshuis</a>. Vanaf deze website kunt u historische
						neerslag- en verdampingsgegevens voor heel Nederland downloaden.
					</p>
					<p>
						Het achtergrondrapport voor informatie over basisgegevens en rasterdata vindt u <a href="https://www.meteobase.nl:8080/meteobase/downloads/fixed/Rapport_Meteobase_definitief.pdf" target="_blank">hier</a>.
						Dit rapport bevat ook nog informatie over de toetsingsdata die tot medio oktober 2015
						op Meteobase aangeboden werden. Per medio oktober 2015 zijn deze data geactualiseerd.
						Uitgebreide toelichting op deze nieuwe data vindt u in dit <a href="https://www.meteobase.nl:8080/meteobase/downloads/fixed/STOWA_2015_10_webversie_LR2.pdf" target="_blank">rapport</a>.
					</p>
					<p>
						In een bijeenkomst op 17 december 2015 is dit laatste onderzoek toegelicht,
						het verslag en de presentaties vindt u <a href="https://www.meteobase.nl:8080/meteobase/downloads/fixed/Verslag_17_december_2015.zip" target="_blank">hier</a>.
					</p>
					<p>
						Deze online dienst is in het leven geroepen om medewerkers van waterschappen en
						adviesbureaus in de watersector te ondersteunen bij het uitvoeren van modelstudies waarvoor
						meteorologische gegevens nodig zijn.
					</p>
					<p>
						Let op: deze website maakt gebruik van cookies. Deze slaan uitsluitend uw gebruikersnaam, bedrijfsnaam en mailadres op ten behoeve van de gebruiksstatistieken en de helpdesk.
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of Introduction Modal-->
<!--Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" role="dialog" aria-labelledby="registrationModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="registrationModalTitle">Registreren</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<p>
						Om data van deze website te kunnen betrekken, dient u allereerst uw naam,
						bedrijfsnaam, e-mailadres en telefoonnummer op te geven.
						Na het aanmelden krijgt u automatisch toegang tot de tabbladen Basisgegevens,
						Rasterdata en Toetsingsdata.
					</p>
					<p>
						Nadat u uw bestelling hebt geplaatst, krijgt u uw sessie-ID te zien en een link
						naar de download-directory. Afhankelijk van de omvang van uw bestelling kan
						het enige tijd duren eer uw gegevens online staan.
					</p>
					<p>
						Meteobase.nl maakt bij het aanbieden van haar diensten gebruik van cookies.
						Dit zijn bijvoorbeeld cookies die gebruikt worden om u, nadat u ingelogd bent,
						te kunnen blijven herkennen. Naast deze functionele cookies worden tevens
						cookies gebruikt voor het bijhouden van websitestatistieken.
					</p>
					<p>
						Op de site van de overheidsinstantie <a href="https://www.waarschuwingsdienst.nl/Risicos/Inbreuk+op+je+privacy/Cookies-+een+bedreiging+voor+uw+privacy.html" target="_blank">Waarschuwingsdienst.nl</a>
						vindt u meer informatie over cookies.
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of Registration Modal-->
<!--Basic Info Modal -->
<div class="modal fade" id="basicInfoModal" tabindex="-1" role="dialog" aria-labelledby="basicInfoModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="basicInfoModalTitle">Basisgegevens</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<p>
						Dit tabblad bevat de basisgegevens zoals geproduceerd door het KNMI.
						Maak eerst de keuze voor uurstations of dagstations, en selecteer daarna op
						de kaart de stations van welke u gegevens wilt downloaden.
						Merk op dat verdampingscijfers alleen beschikbaar zijn op de uurstations.
					</p>
					<p>
						Wanneer u de cursor van uw muis boven een station beweegt, verschijnt de
						naam van het desbetreffende station.
					</p>
					<p>
						Wilt u de gedownloade neerslaggegevens toepassen op een groot gebiedsoppervlak? Dan moet worden gecorrigeerd voor het gebiedsoppervlak. Voor de nieuwe statistiek van 2019 kan deze correctie worden uitgerekend met de app onder het menu Statistiek - Oppervlaktereductie. Voor de 'oude' statistiek uit 2015 is hiervoor een Excel-macro beschikbaar dit kan  <a href="https://85.214.197.176:8080/meteobase/downloads/fixed/Neerslagreductie.zip" target="_blank">hier</a> worden gevonden.
					</p>
					<p>
						DEZE GEGEVENS MOGEN VRIJELIJK WORDEN GEBRUIKT MITS DE VOLGENDE BRONVERMELDING
						WORDT GEGEVEN: KONINKLIJK NEDERLANDS METEOROLOGISCH INSTITUUT (KNMI)
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of Basic Info Modal-->
<!--RasterData Modal -->
<div class="modal fade" id="rasterDataModal" tabindex="-1" role="dialog" aria-labelledby="rasterDataModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="rasterDataModalTitle">Rasterdata</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<!-- Nav tabs -->
					<ul id="rasterDataTabs" class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#introduction" aria-controls="home" role="tab" data-toggle="tab">Introductie</a></li>
						<li role="presentation"><a href="#algorithms" aria-controls="profile" role="tab" data-toggle="tab">Algoritmes</a></li>
					</ul>
					
					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="introduction">
							<p>Grids met neerslag en verdamping.</p>
							<p>
								Veel (geo)hydrologische simultatieprogramma's vragen om meteorologische data in rasterformaat.
								Andere modellen vragen juist om gebiedsgemiddelde neerslagvolumes die zijn afgeleid uit
								de ruimtelijk verdeelde neerslag op een gebied.
							</p>
							<p>
								Om aan dergelijke wensen van hydrologen tegemoet te komen, biedt deze sectie u de
								mogelijkheid om neerslag en verdamping voor een vrij te kiezen deelregio te downloaden
								in rasterformaat.
							</p>
							<p>
								De neerslagvolumes bestaan uit radargegevens die aan meetwaarden van 216 grondstations
								zijn geijkt, en kunnen daarom worden gebruikt voor kalibratiedoeleinden.
							</p>
						</div>
						<div role="tabpanel" class="tab-pane" id="algorithms">
							<p>
								Informatie over de algoritmes.
							</p>
							<p>
								Als u geinteresseerd bent in de wijze waarop de ruwe radargegevens werden geijkt aan
								de meetwaarden van de grondstations, nodigen wij u van harte uit om
								<a href="https://www.meteobase.nl/meteobase/downloads/fixed/Rapport_Meteobase_definitief.pdf" target="_blank">hier</a> het rapport
								van HKV - lijn in water te downloaden.
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of RasterData Modal-->
<!--TestingData Modal -->
<div class="modal fade" id="testingDataModal" tabindex="-1" role="dialog" aria-labelledby="testingDataModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="testingDataModalTitle">Toetsingsdata</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<p>
						In deze sectie kunt u meteorologische gegevens downloaden ten behoeve van statistische analyses
						zoals hoogwaterstudies.
					</p>
					<p>
						Omdat KNMI-station De Bilt beschikt over de langste homogene dataset van Nederland (1906-heden),
						zijn alle gegevens in deze sectie ontleend aan meetwaarden van dit station.
					</p>
					<p>
						Station De Bilt kan echter niet representatief worden geacht voor heel Nederland.
						Daarom heeft KNMI in 2019 een nieuwe regioverdeling gepubliceerd voor jaarrondstatistiek en voor het winterseizoen (NDJF). De regioverdeling voor jaarrond bestaat uit drie klassen:
						L, R en H. De regioverdeling voor het winterseizoen bestaat uit vier klassen: LL, L, R en H. Voor elk van deze klassen is een eigen langjarige
						tijdreeks en stochastiek afgeleid.
					</p>
					<p>
						Download deze regioverdeling in shape-formaat <a href="https://www.meteobase.nl/meteobase/downloads/fixed/Klimaatregios.zip" target="_blank">hier</a>. U ontvangt deze regioverdeling overigens ook bij uw bestelling.
						De regioverdeling die hoort bij de 'oude' statistiek uit 2015 kan <a href="https://www.meteobase.nl/meteobase/downloads/fixed/Regios.zip" target="_blank">hier</a> nog worden gevonden.
					</p>
					<p>
						Wilt u de gedownloade neerslaggegevens toepassen op een groot gebiedsoppervlak? Dan moet worden gecorrigeerd voor het gebiedsoppervlak. Voor de nieuwe statistiek van 2019 kan deze correctie worden uitgerekend met de app onder het menu Statistiek - Oppervlaktereductie. Voor de 'oude' statistiek uit 2015 is hiervoor een Excel-macro beschikbaar dit kan  <a href="https://www.meteobase.nl/meteobase/downloads/fixed/Neerslagreductie.zip" target="_blank">hier</a> worden gevonden.
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of TestingData Modal-->
<!--Colophon Modal -->
<div class="modal fade" id="colophonModal" tabindex="-1" role="dialog" aria-labelledby="colophonModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="colophonModalTitle">Colofon</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<p>
						Deze online service werd ontwikkeld in opdracht van <a
						href="www.hetwaterschapshuis.nl" target="_blank">Het Waterschapshuis</a>.
					</p>
					<p>
						Alle meteorologische gegevens werden ontsloten en bewerkt door
						<a target="_blank" href="https://www.hkv.nl">HKV - lijn in water</a>.
						Het bijbehorende rapport kunt u <a href="https://www.meteobase.nl/meteobase/downloads/fixed/Rapport_Meteobase_definitief.pdf" target="_blank">hier</a> downloaden.
					</p>
					<p>
						Station De Bilt kan echter niet representatief worden geacht voor heel Nederland.
						Daarom heeft KNMI een regioverdeling gepubliceerd die bestaat uit vier klassen:
						Mild, De Bilt, Hevig en Zeer hevig. Voor elk van deze klassen is een eigen langjarige
						tijdreeks en stochastiek afgeleid.
					</p>
					<p>
						De ontwikkeling van de website, database en online hosting was in handen
						van <a href="https://hydroconsult.nl" target="_blank">Hydroconsult</a> met medewerking van
						<a href="https://geopro.nl" target="_blank">Geopro</a> en <a href="https://prodevel.solutions" target="_blank">Prodevel</a>.
					</p>
					<p>
						Supportvragen kunnen worden gericht aan het
						algemene e-mailadres van meteobase: <a href="mailto:info@meteobase.nl">info@meteobase.nl</a>
					</p>
					<p>
						Aan de teksten en gegevens op deze website heeft Het Waterschapshuis veel zorg en aandacht besteed om de
						juistheid en actualiteit ervan te waarborgen. Het kan desondanks voorkomen dat er fouten
						in zijn geslopen. Mocht u informatie tegenkomen die in uw ogen niet correct (meer) is,
						of verouderd, laat het ons weten. Het Waterschapshuis aanvaardt geen aansprakelijkheid voor deze fouten.
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of Colophon Modal-->

<!--Literature Info Modal -->
<div class="modal fade" id="literatureModal" tabindex="-1" role="dialog" aria-labelledby="literatureModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="literatureModalTitle">Literatuur</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<h5>Meteobase</h5>
					<ul>
						<li>
							Hakvoort, H., et al., 2013,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Hakvoort2013_Stromingen_Meteobase.pdf" target="_blank">
								“Meteobase: online neerslag- en referentiegewasverdampingsdatabase voor het Nederlandse waterbeheer”
							</a>
							, Stromingen 19(2),  pp. 75-84.
						</li>
						<li>
							Versteeg R., et al., 2012, <a href="https://www.meteobase.nl" target="_blank">www.meteobase.nl</a>:
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Versteeg2012_HKV_Rapport Meteobase.pdf" target="_blank">
								online-archief van neerslag- en verdampingsgegevens voor het waterbeheer
							</a>
							, HKV Rapport 2197.
						</li>
						<li>
							Wolters E., et al., 2013,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Wolters2013_Meteorologica_Meteobase.pdf" target="_blank">
								“Meteobase: online neerslag- en referentiegewasverdampingsdatabase voor het Nederlandse waterbeheer”
							</a>
							, Meteorologica 2, pp. 15-18.
						</li>
					</ul>
					<h5>WIWB</h5>
					<ul>
						<li>
							Het Waterschapshuis,
							<a href="https://portal.hydronet.com/data/files/Technische%20Instructies%20WIWB%20API.pdf" target="_blank">
								“Technische Handleiding Weer Informatie Waterbeheer (WIWB) API”
							</a>
						</li>
						<li>
							Hydrologic,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/P951_FAQ_WIWB_Februari_2020.pdf" target="_blank">
								“FAQ WIWB Februari 2020”
							</a>
							.
						</li>
						<li>
							Hydrologic,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/WIWB_aanmelden_WIWB_API.xlsx" target="_blank">
								“Formulier voor aanvragen toegang WIWB”
							</a>
						</li>
						<li>
							Het Waterschapshuis,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/P951_Voorbeeldverzoeken_WIWB_API.pdf" target="_blank">
								“Voorbeeldverzoeken WIWB-API”
							</a>
							.
						</li>
					</ul>
					<h5>Neerslagrasters</h5>
					<ul>
						<li>
							HKV,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Toelichting_Meteobase_rasters_2018.pdf" target="_blank">
								“Toelichting Meteobase-rasters 2018”
							</a>
							, Memorandum HKV Lijn-in-water, 27 maart 2019; 
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/statistieken_reanalysis_validatie_2018.pdf" target="_blank">
								“Bijlage: analyse neerslagrasters 2018”; 
							</a>							
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/memo_verwachtingskwaliteit_2021.pdf" target="_blank">
								“Nauwkeurigheid meteorologische verwachtingen 2021”
							</a>, Memorandum HKV Lijn-in-water, 8 maart 2022.
						</li>
						<li>
							Holleman, I. (2007),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Holleman2007_MeteorolApp_radarComposites.pdf" target="_blank">
								“Bias adjustment and long-term verification of radar-based precipitation estimates”
							</a>
							, Meteorol. Appl. 14, pp. 195–203.
						</li>
						<li>
							Hurkmans, R. en Hakvoort, H. (2015),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Hurkmans2015_HKV_memo_Beschrijving_aanpassingen_neerslagrasters.pdf" target="_blank">
								“Beschrijving aanpassingen aan neerslagrasters”
							</a>
							, HKV memo PR2197.50.
						</li>
						<li>
							Kallen, M.-J.  (2013),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Kallen2012_constructie_neerslagrasters.pdf" target="_blank">
								”Maken en controleren van rasters met neerslaghoeveelheden”
							</a>
							, HKV rapport PR2197.
						</li>
						<li>
							Pebesma, E.,
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Pebesma_Gstat_R_documentation.pdf" target="_blank">
								“Documentation of the R-package ‘gstat’, version 1.1.4: spatial and spatio-temporal geostatistical modelling, prediction and simulation”
							</a>.
						</li>
						<li>
							Schuurmans, J.M., et al. (2007),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Schuurmans2007_JHM_interpolation.pdf" target="_blank">
								“Automatic Prediction of High-Resolution Daily Rainfall Fields for Multiple Extents: The Potential of Operational Radar”
							</a>
							, J. Hydrometeorol. 8, pp. 1204-1224.
						</li>
					</ul>
					<h5>Referentiegewasverdamping</h5>
					<ul>
						<li>
							Allen, R.A., et al. (1998),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Allen1998_FOA56_ET0_PM.pdf" target="_blank">
								“Crop Evapotranspiration (guidelines for computing crop water requirements)"
							</a>
							, FAO Irrigation and Drainage Paper No. 56.
						</li>
						<li>
							De Bruin, H.A.R. (2014),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/deBruin2014_Meteorologica_referentiegewasverdamping.pdf" target="_blank">
								“Over referentiegewasverdamping”
							</a>
							, Meteorologica 1, pp. 15-20.
						</li>
						<li>
							De Bruin, H.A.R. (1987),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Hooghart1987_Evaporation_and_weather_proceedings_no39.pdf" target="_blank">
								“From Penman to Makkink”
							</a>
							. In: Hooghart, J.C., Ed., Proceedings and Information: TNO Committee on Hydrological Research N°39. The Netherlands Organization for Applied Scientific Research TNO, Den Haag, 5-31.
						</li>
					</ul>
					<h5>Neerslagstatistiek</h5>
					<ul>
						<li>
							STOWA, (2019),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/STOWA_2019-19_neerslagstatistieken.pdf" target="_blank">
								“Neerslagstatistiek en -reeksen voor het waterbeheer 2019”
							</a>
							, rapport 19-2019.
						</li>
						<li>
							Leijnse, H., (2019),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/rapport_correctiemethoden.pdf" target="_blank">
								“Analyse van verschillende methoden voor het combineren van radar- en regenmetergegevens”
							</a>
							, KNMI. <a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/samenvatting_rapport_correctiemethoden.pdf" target="_blank">Samenvatting.</a>
						</li>
						<li>
							Vos, L.W. de, et al., (2019),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/onderzoek_kwaliteitscontrole_voor_regenmeters_door_Lotte_de_Vos_Engels.pdf" target="_blank">
								“Crowdsourced personal weather stations show great potential for operational rainfall monitoring”
							</a>
							, Ingediend bij Geophysical Research Letters. <a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/samenvatting_onderzoek_kwaliteitscontrole_neerslagmetingen_WIWB_Nederlands.pdf" target="_blank">Nederlandstalige samenvatting.</a>
						</li>
						<li>
							Beersma, J. et al., (2015),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Beersma2015_STOWArapport_actualisatie_meteogegevens.pdf" target="_blank">
								“Actualisatie meteogegevens voor waterbeheer 2015. Deel 1 Neerslag- en verdampingsreeksen. Deel 2 Statistiek van de extreme neerslag”
							</a>
							, STOWA Rapport 2015-10.
						</li>
						<li>
							Beersma, J. et al., (2018),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Beersma2018_STOWAneerslagstatistiek_korteduren.pdf" target="_blank">
								“Actualisatie 2018. Neerslagstatistieken voor korte duren”
							</a>
							, STOWA Rapport 2015-10.
						</li>
						<li>
							Overeem, A. en A. Buishand (2012),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Overeem2012_KNMIrapport_statistiek_extreme_gebiedsneerslag.pdf" target="_blank">
								“Statistiek van extreme gebiedsneerslag in Nederland”
							</a>
							, KNMI Technical report TR-332.
						</li>
						<li>
							Smits, I. et al., (2004),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Smits2004_STOWArapport_neerslagstatistiek.pdf" target="_blank">
								“Statistiek van extreme neerslag in Nederland”
							</a>
							, STOWA Rapport 2004-26.
						</li>
						<li>
							Velner, R. et al. (2011),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Velner2011_STOWArapport_standaard_werkwijze_toetsen.pdf" target="_blank">
								“Standaard werkwijze voor de toetsing van watersystemen aan de normen voor regionale wateroverlast”
							</a>
							, STOWA Rapport 2011-31.
						</li>
					</ul>
					<h5>SAT Data</h5>
					<ul>
						<li>
							Vandersat (2021),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Plausibiliteits_rapport_2021_Satdata_3_0.pdf" target="_blank">
								“Plausibiliteitstoets SATDATA 3.0”
							</a>
							, Gemaakt in opdracht van Het Waterschapshuis.
						</li>
						<li>
							Vandersat (2020),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/Oplevering_2020_Satdata_3_0_002.pdf" target="_blank">
								“Oplevering 2020 Satdata 3.0 (002).pdf”
							</a>
							, Gemaakt voor het SAT-WATER consortium.
						</li>					
						<li>
							eLEAF. (2017),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/20171101_Methodiek SAT-DATA_eLEAF_v4.pdf" target="_blank">
								“Satelliet-gebaseerde verdampingsdata voor Nederland”
							</a>
							, Gemaakt voor het SAT-WATER consortium.
						</li>					
						<li>
							eLEAF. (2017),
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/20171101_Plausibiliteit_SAT-DATA_eLEAF_v4.pdf" target="_blank">
								“Plausibiliteitstoets SAT DATA 2.0”
							</a>
							, Gemaakt voor het SAT-WATER consortium.
						</li>					
					</ul>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!--End of Literature Modal-->
<!--Polygon Points Modal -->
<div class="modal fade" id="polygonModal" tabindex="-1" role="dialog" aria-labelledby="literatureModalTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="literatureModalTitle">Coordinaten</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<form method="POST" id="polygonCoordinates" action="" class="polygonCoordinates">
						<div class="form-group col-md-6 col-sm-12 col-xs-12">
							<label for="zwx">ZW X</label>
							<input type="text" name="zwx" id="zwx" class="form-control" value="<?php if ($_COOKIE['minX']) { echo $_COOKIE['minX'];} else { echo '';} ?>" aria-describedby="name">
						</div>
						<div class="form-group col-md-6 col-sm-12 col-xs-12">
							<label for="zxy">ZW Y</label>
							<input type="text" id="zwy" name="zwy" class="form-control" value="<?php if ($_COOKIE['minY']) { echo $_COOKIE['minY'];} else { echo '';} ?>" aria-describedby="email">
						</div>
						<div class="form-group col-md-6 col-sm-12 col-xs-12">
							<label for="nox">NO X</label>
							<input type="text" name="nox" id="nox" class="form-control" value="<?php if ($_COOKIE['maxX']) { echo $_COOKIE['maxX'];} else { echo '';} ?>" aria-describedby="name">
						</div>
						<div class="form-group col-md-6 col-sm-12 col-xs-12">
							<label for="noy">NO Y</label>
							<input type="text" id="noy" name="noy" class="form-control" value="<?php if ($_COOKIE['maxY']) { echo $_COOKIE['maxY'];} else { echo '';} ?>" aria-describedby="email">
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
				<button type="button" class="btn submit-btn btn-primary">Pas Aan</button>
			</div>
		</div>
	</div>
</div>
<!--End of Literature Modal-->
<!-- Stochasten interactief Modal -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<!--Alert Placeholder-->
<div id="alert_placeholder"></div>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.js"></script>
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<script src="js/jquery.stellar.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/jquery.validate.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/underscore-min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/stations_number.js" type="text/javascript" charset="utf-8"></script>
<script src="js/dropzone.js" type="text/javascript" charset="utf-8"></script>
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
<script src="https://openlayers.org/en/v3.20.1/build/ol.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<!--<script src="http://malsup.github.com/jquery.form.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.3.15/proj4.js"></script>
<script src="https://epsg.io/28992.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="js/download.js"></script>
<script type="text/javascript">


	//change 
	var cartServerUrl = 'regenduurlijnen/api/stats/returnperiod/STOWA2019';
	var vaxis = 'volume [mm]';
	var chartname = 'herhalingstijd_stowa2019';
	var vscale = 'none'
	var vMax = 250;
	var valMax = 300;
	function seturl(url){
	cartServerUrl = url;
	};
	function setchart(name){
	chartname = name;
	};
	function setvaxis(name){
	vaxis = name;
	};
	function setscale(scale){
	vscale = scale;
	};
	function setVMax(max) {
		vMax = max;
	}
    google.charts.load('43', {packages: ['corechart'], 'language': 'nl'});
    google.charts.setOnLoadCallback(function () {
        setupform();
    });
   google.charts.setOnLoadCallback(function () {
        setupform2();
    });

    function setupform() {
        var form = document.getElementById('input_par_form');
        
        form.onsubmit = function() {
			console.log("form send!")
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST',cartServerUrl, true);
            xhr.setRequestHeader("Access-Control-Allow-Credentials", "true")
            xhr.withCredentials = true;
            xhr.send(formData);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    drawChart(xhr.responseText);
                }
            };

            return false;
        }
    };

    function setupform2() {
        var form = document.getElementById('input_par_form2');
        
        form.onsubmit = function() {
			//console.log("form send!")
            var formData = new FormData(form);
            var xhr = new XMLHttpRequest();
            xhr.open('POST','regenduurlijnen/api/stats/area', true);
            xhr.setRequestHeader("Access-Control-Allow-Credentials", "true")
            xhr.withCredentials = true;
            xhr.send(formData);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
					//console.log("api-area succesvol!")
                    drawChart2(xhr.responseText);
                }
            };

            return false;
        }
    };
	
    var ImgURL;
	var chartImageName;
	var csvString;
	function drawChart(jsonData) {
		if (jsonData === "false") {
		document.getElementById(chartname).innerHTML = "De combinatie van klimaat, periode en scenario is niet beschikbaar in deze studie.";
		}
        else {       
        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);
		var climate = $('#climate').val();
		var chartType = chartname.charAt(0).toUpperCase() + chartname.slice(1)
		var period = $('#season').val();
		var scenario = $('#scenario').val();
		var chartNameParts = [chartType, climate, period, scenario];
		var chartTitle = chartNameParts.join(' ');
        chartImageName = chartTitle;
        var options = {
			title: chartTitle,
            legend: 'right',
            curveType: 'function',
            hAxis: {title: 'duur [uren]', scaleType: 'non'},
            vAxis: {title: vaxis, scaleType: vscale, viewWindow:{max: vMax}, minorGridlines: {count: 10}}
        };
        
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById(chartname));
        google.visualization.events.addListener(chart, 'ready', function () {
            ImgURL = chart.getImageURI()
			var csvData = data;
			csvData.insertRows(0, 1);
			for (i = 0; i < csvData.getNumberOfColumns(); i++) {
				csvData.setCell(0, i, null, data.getColumnLabel(i));
			}
			
			csvString = myDataTableToCsv(csvData);
			
			onChartDrawn();
        });
        chart.draw(data, options);
    }}
	
	
	function drawChart2(jsonData) {
		var chartname2 = 'oppervlaktereductie'
		if (jsonData === "false") {
		document.getElementById(chartname2).innerHTML = "De combinatie van klimaat, periode en scenario is niet beschikbaar in deze studie.";
		}
        else {       
        // Create our data table out of JSON data loaded from server.
        var data = new google.visualization.DataTable(jsonData);
		var area = $('#area').val();
		var chartType = chartname2.charAt(0).toUpperCase() + chartname2.slice(1)
		var chartNameParts = [chartType, area, 'km2'];
		var chartTitle = chartNameParts.join(' ');
        chartImageName = chartTitle;
        var options = {
			title: chartTitle,
            legend: 'right',
            curveType: 'function',
            hAxis: {title: 'duur [uren]', scaleType: 'log'},
            vAxis: {title: 'oppervlakte reductie [-]', scaleType: 'non', viewWindow:{max: 1}, minorGridlines: {count: 10}}
        };
        
        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.LineChart(document.getElementById(chartname2));
        google.visualization.events.addListener(chart, 'ready', function () {
            ImgURL = chart.getImageURI()
			var csvData = data;
			csvData.insertRows(0, 1);
			for (i = 0; i < csvData.getNumberOfColumns(); i++) {
				csvData.setCell(0, i, null, data.getColumnLabel(i));
			}
			csvString = myDataTableToCsv(csvData);
			
			onChartDrawn();
        });
        chart.draw(data, options);
    }}
	
var myDataTableToCsv = function(table){
		var csv = "";
		var delimiter = ";";

		if (!table) {
			return csv;
		} 
		for (i = 0; i < table.getNumberOfRows(); i++) {
			for(var j = 0; j < table.getNumberOfColumns(); j++) {
				j > 0 && (csv += delimiter);

				var el = table.getFormattedValue(i, j);
				el = el.replace(/"/g, '""');
				if (el.indexOf(delimiter) !== -1 || el.indexOf("\n") !== -1 || el.indexOf('"') !== -1) {
					el = '"' + el + '"';
				}
				csv += el;
			}
			csv+="\n"
		}
		return csv;
	}
	
    </script>
<script>
    function downloadChart() {
        download(ImgURL, chartImageName + '.png', "image/png");
    }
	function downloadCSV() {
		console.log(chartImageName);
		download(csvString, chartImageName + '.csv', 'text/csv');
	}
</script>


<script>
// temporary script to disable Internet Explorer
function detectIE() {
    
    var ua = window.navigator.userAgent;

    var msie = ua.indexOf('MSIE ');
    if (msie > 0) {
        // IE 10 or older => return version number
        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
    }

    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
        // IE 11 => return version number
        var rv = ua.indexOf('rv:');
        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
    }

    // other browser
    return false;

};

function checkIE() {
    browser = detectIE();
    if(browser != false){
        console.log('alert!')
        alert('Deze applicatie werkt niet goed in uw browser: Internet Explorer ' + browser + '. U kunt deze app gebruiken in Microsoft Edge (standaard beschikbaar in Windows 10), Chrome of Firefox.')
        document.getElementById("chartDocument").innerHTML = 'Deze applicatie werkt niet goed in uw browser: Internet Explorer ' + browser + '. U kunt deze app gebruiken in Microsoft Edge (standaard beschikbaar in Windows 10), Chrome of Firefox.'
};
};



</script>



<div class="modal fade" id="chartDialogStochasten" tabindex="-1" role="dialog" aria-labelledby="chartDialogStochastenTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" >
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="chartDialogStochastenTitle">Regenduurlijnen</h4>
			</div>
			<div class="modal-body" id="chartDocument">
				<div class="row">
					<div class="col-sm-5 col-md-4 col-lg-3">
						<form id="input_par_form">
							<div class="form-group">
								
								<label for="climate">Zichtjaar</label>
								<select class="custom-select custom-select-sm form-control" id="climate" name="climate">
									<option value="2014">2014</option>
									<option value="2030">2030</option>
									<option value="2050">2050</option>
									<option value="2085">2085</option>
								</select>
							</div>
							<div class="form-group">
								<label for="season">Periode:</label>
								<select class="custom-select custom-select-sm form-control" id="season" name="season">
									<option value="jaarrond">Jaarrond</option>
									<!--option value="zomer">Zomerperiode</option-->
									<option value="winter">Winterperiode</option>
								</select>
							</div>

							<div class="form-group">
								<label for="scenario">Scenario:</label>
								<select  class="subcat custom-select custom-select-sm form-control" id="scenario" name="scenario">
									<optgroup data-rel="2014">
										<option value="-">-</option>
									</optgroup>
									<optgroup data-rel="2030">
										<option value="lower">lower</option>
										<option value="centr">centr</option>
										<option value="upper">upper</option>
									</optgroup>
									<optgroup data-rel="2050">
										<option value="GL_lower">GL_lower</option>
										<option value="GL_centr">GL_centr</option>
										<option value="GL_upper">GL_upper</option>
										<option value="GH_lower">GH_lower</option>
										<option value="GH_centr">GH_centr</option>
										<option value="GH_upper">GH_upper</option>
										<option value="WL_lower">WL_lower</option>
										<option value="WL_centr">WL_centr</option>
										<option value="WL_upper">WL_upper</option>
										<option value="WH_lower">WH_lower</option>
										<option value="WH_centr">WH_centr</option>
										<option value="WH_upper">WH_upper</option>
									</optgroup>
									<optgroup data-rel="2085">
										<option value="GL_lower">GL_lower</option>
										<option value="GL_centr">GL_centr</option>
										<option value="GL_upper">GL_upper</option>
										<option value="GH_lower">GH_lower</option>
										<option value="GH_centr">GH_centr</option>
										<option value="GH_upper">GH_upper</option>
										<option value="WL_lower">WL_lower</option>
										<option value="WL_centr">WL_centr</option>
										<option value="WL_upper">WL_upper</option>
										<option value="WH_lower">WH_lower</option>
										<option value="WH_centr">WH_centr</option>
										<option value="WH_upper">WH_upper</option>
									</optgroup>
								</select>
							</div>
							<div class="form-group" id="extra-data">
								<label for="value">Extra herhalingstijd:</label>
								<input type="number" class="form-control" step="1" min="1" max="1000" id="value" name="value">
							</div> 
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-calculator"></i>
								Bereken
							</button>
						</form>
						<div>
							<p>
							<br><br>
							<font size="1">
							Bron:
							<a href="https://www.meteobase.nl/meteobase/downloads/fixed/literatuur/STOWA_2019-19_neerslagstatistieken.pdf" target="_blank">
									Stowa (2019)
							</a>
							</font>
							</p>
						</div>
						<div class="svgWarningWrapper" style="margin-top: 20px;display:none;"  >
							<a href="#" class="modal-opener" data-toggle="modal" data-target="#chartDialogStochastenWarning">
								<i style="font-size:28px" class="fa">&#xf06a;</i>
							</a>
						</div>
					</div>
					<!--<div class="col-sm-7 col-md-8 col-lg-9">-->
					<div class="col-sm-7 col-md-8 col-lg-9">
						<div id="chartTabs">
							<ul>
								<li>
									<!--<a href="#fragment-1" onclick="seturl('https://h2710502:5000/api/stats/returnperiod/STOWA2019'); setVMax(250); setchart('herhalingstijd_stowa2019'); setvaxis('volume [mm]');setscale('none'); onChartTabChange('Extra herhalingstijd:', '', 1000, true); $('#input_par_form').trigger('submit');">
										<span>Herhalingstijd</span>
									</a>-->
									<a href="#fragment-1" onclick="seturl('regenduurlijnen/api/stats/returnperiod/STOWA2019'); setVMax(250); setchart('herhalingstijd_stowa2019'); setvaxis('volume [mm]');setscale('none'); onChartTabChange('Extra herhalingstijd:', '', 1000, true); $('#input_par_form').trigger('submit');">
										<span>Herhalingstijd</span>
									</a>
								</li>
								<!--<li>
									<a href="#fragment-2" onclick="seturl('https://www.meteobase.nl:5000/api/stats/returnperiod/STOWA2018'); setVMax(300); setchart('herhalingstijd_stowa2018'); setvaxis('volume [mm]');setscale('none'); onChartTabChange('Extra herhalingstijd:', '', 10000, false); $('#input_par_form').trigger('submit');">
										<span>Herhalingstijd (STOWA 2018)</span>
									</a>
								</li>-->
								<li>
									<a href="#fragment-3" onclick="seturl('regenduurlijnen/api/stats/volume/STOWA2019'); setVMax(1000); setchart('volume_stowa2019'); setvaxis('herhalingstijd [jaren]');setscale('log'); onChartTabChange('Extra volume:', '', 150, true); $('#input_par_form').trigger('submit');">
										<span>Volume</span>
									</a>
									<!--<a href="#fragment-3" onclick="seturl('https://h2710502:5000/api/stats/volume/STOWA2019'); setVMax(1000); setchart('volume_stowa2019'); setvaxis('herhalingstijd [jaren]');setscale('log'); onChartTabChange('Extra volume:', '', 150, true); $('#input_par_form').trigger('submit');">
										<span>Volume</span>
									</a>-->
								</li>
								<!--<li>
									<a href="#fragment-4" onclick="seturl('https://www.meteobase.nl:5000/api/stats/volume/STOWA2018'); setVMax(10000); setchart('volume_stowa2018'); setvaxis('herhalingstijd [jaren]');setscale('log'); onChartTabChange('Extra volume:', '', 250, false); $('#input_par_form').trigger('submit');">
										<span>Volume (STOWA 2018)</span>
									</a>
								</li>-->
							</ul>
							<div id="fragment-1">
								<div id="herhalingstijd_stowa2019" style="border: 1px solid #ccc; width: 100%; height: 100%; min-height: 410px;">selecteer input en druk op 'bereken'. <br>Let op: kan een minuutje duren!</div>
							</div>
							<!--<div id="fragment-2">
								<div id="herhalingstijd_stowa2018" style="border: 1px solid #ccc; width: 100%; height: 100%; min-height: 410px;">selecteer input en druk op 'bereken'</div>
							</div>-->
							<div id="fragment-3">
								<div id="volume_stowa2019" style="border: 1px solid #ccc; width: 100%; height: 100%; min-height: 410px;">selecteer input en druk op 'bereken' <br>Let op: kan een minuutje duren!</div>
							</div>
							<!--<div id="fragment-4">
								<div id="volume_stowa2018" style="border: 1px solid #ccc; width: 100%; height: 100%; min-height: 410px;">selecteer input en druk op 'bereken'</div>
							</div>-->
						</div>
						<div id="chartDownloadButtons" class="float-right" style="margin-top: 5px;">
							<button class="btn btn-primary" onclick="downloadChart()" disabled>
								<i class="fas fa-file-download"></i>
								Download afbeelding
							</button>
							<button class="btn btn-primary" onclick="downloadCSV()" disabled>
								<i class="fas fa-file-download"></i>
								Download CSV
							</button>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="chartDialogOppervlaktereductie" tabindex="-1" role="dialog" aria-labelledby="chartDialogAreaTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" >
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="chartDialogAreaTitle">Oppervlaktereductie</h4>
			</div>
			<div class="modal-body" id="AreachartDocument">
				<div class="row">
					<div class="col-sm-5 col-md-4 col-lg-3">
						<form id="input_par_form2">
							<div class="form-group" id="Oppervlak">
								<label for="value">Oppervlak [km2]:</label>
								<input type="number" class="form-control" step="0.1" min="6" max="965" id="area" name="area">
							</div> 
							<button type="submit" class="btn btn-primary">
								<i class="fa fa-calculator"></i>
								Bereken
							</button>
						</form>
					</div>
					<div class="col-sm-7 col-md-8 col-lg-9">
						<div id="area_chartTab">
							<div id="area_fragment-1">
								<div id="oppervlaktereductie" style="border: 1px solid #ccc; width: 100%; height: 100%; min-height: 400px;">selecteer input en druk op 'bereken'</div>
							</div>
						</div>
						<div id="chartDownloadButtons" class="float-right" style="margin-top: 5px;">
							<button class="btn btn-primary" onclick="downloadChart()">
								<i class="fas fa-file-download"></i>
								Download afbeelding
							</button>
							<button class="btn btn-primary" onclick="downloadCSV()">
								<i class="fas fa-file-download"></i>
								Download CSV
							</button>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Chart warning Modal -->
<div class="modal fade" id="chartDialogStochastenWarning" tabindex="-1" role="dialog" aria-labelledby="chartDialogStochastenWarningTitle">
	<div class="modal-dialog" role="document">
		<div class="modal-content" style="top:150px;">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="introductionModalTitle">Aanvullende informatie</h4>
			</div>
			<div class="modal-body">
				<div class="col-md-12">
					<p>
						De regenduurlijnen met herhalingstijden vanaf 3000 jaar vertonen bij duren langer dan 8 uur een lichte daling. Dit komt omdat is 
						gekozen voor één extreme waardenverdeling met de bijbehorende parameter-set die het geheel van regenduurlijnen het beste beschrijft. 
						In het uiterste van het bereik treedt dit effect op. De vergelijking geeft een schatting van extreme neerslaghoeveelheden. 
						Deze schatting heeft een onzekerheidsmarge die bij toenemende herhalingstijden en duren toeneemt. De schatting mag naar 
						boven worden aangepast.
					</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Sluiten</button>
			</div>
		</div>
	</div>
</div>
<!-- Chart warning Modal -->

<script>

function onChartDrawn() {
	toggleDownloadChartInfoButtons(false);
}

function onChartTabChange(label, value, maxValue, hideExclamationMark) {
	// disable download buttons untill the data is retrieved
	toggleDownloadChartInfoButtons(true);
	// Change label value and max value of extra data input field
	var extraDataElement = $('#extra-data');
	var labelEl = extraDataElement.find('label');
	var inputEl = extraDataElement.find('#value');
	labelEl.html(label); 
	inputEl.val(value);
	inputEl.attr({"max" : maxValue});
	// toggle exclamation marker
	var exlMarkEl = $('#chartDialogStochasten .svgWarningWrapper');
	if (hideExclamationMark) {
		exlMarkEl.hide();
	} else {
		exlMarkEl.show();
	}
}

function toggleDownloadChartInfoButtons(disable) {
	var buttons = $('#chartDialogStochasten #chartDownloadButtons button');
	if (disable) {
		buttons.attr('disabled', 'disabled');
	} else {
		buttons.removeAttr('disabled');
	}
}

$(function(){
    
    var $cat = $("#climate"),
        $subcat = $(".subcat");
    
    var optgroups = {};
    
    $subcat.each(function(i,v){
    	var $e = $(v);
    	var _id = $e.attr("id");
			optgroups[_id] = {};
			$e.find("optgroup").each(function(){
      	var _r = $(this).data("rel");
        $(this).find("option").addClass("is-dyn");
      	optgroups[_id][_r] = $(this).html();
			});
    });
    $subcat.find("optgroup").remove();
	$subcat.prop("disabled", true);
    
    var _lastRel;
    $cat.on("change",function(){
        var _rel = $(this).val();
        if(_lastRel === _rel) return true;
        _lastRel = _rel;
        $subcat.find("option").attr("style","");
        $subcat.val("");
        $subcat.find(".is-dyn").remove();
        if(!_rel) return $subcat.prop("disabled",true);
		if (_rel == '2014') { return $subcat.prop("disabled", true) } else { $subcat.prop("disabled", false) };
        $subcat.each(function(){
        	var $el = $(this);
          var _id = $el.attr("id");
          $el.append(optgroups[_id][_rel]);
        });
        $subcat.prop("disabled",false);
    });
    
});
</script>
<!--End of  Stochasten interactief Modal-->



<script type="text/javascript">
	
	$(function(){
		var mbAlert = function (message, typeClass) {
		$('#alert_placeholder').html(
		'<div class="alert ' + typeClass + '">'+message+'</div>'
		);
		$('#alert_placeholder').fadeIn();
		setTimeout(function () {
			$('#alert_placeholder').fadeOut();
		}, 3000);
	};
	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}
	
	function eraseCookie(name) {
		createCookie(name,"",-1);
	}
	function readCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		} 
		return "";
	
	}
	if (readCookie('redirect_after_login')){
		var newLocation = readCookie('redirect_after_login');
		eraseCookie('redirect_after_login');
		window.location.href = newLocation;
	}
	function removeMeteostationsCookie() {
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		_.each(ca, function(cookie){
			var cookieParts = cookie.split('=');
			var cName = cookieParts[0].substring(1, cookieParts[0].length);
			if (cName.indexOf('ms') == 0) {
				eraseCookie(cName);
			}
		});
	}
	function checkMeteostationsCookie() {
		var containMs = false;
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		_.each(ca, function(cookie){
			var cookieParts = cookie.split('=');
			var cName = cookieParts[0].substring(1, cookieParts[0].length);
			if (cName.indexOf('ms') == 0) {
				containMs = true;
			}
		});
		return containMs;
	}
	
	var getText = function(feature) {
		
		var text = feature.get('name');
		
		return text;
	};
	
	var createTextStyle = function(feature) {
		var align = 'center';
		var baseline = 'middle';
		var size = '16px';
		var offsetX = 0;
		var offsetY = -30;
		var weight = 'bold';
		var rotation = 0.00;
		var font = weight + ' ' + size + ' ' + 'Arial';
		var fillColor = '#000000';
		var outlineColor = '#ffffff';
		var outlineWidth = '3';
		
		return new ol.style.Text({
			textAlign: align,
			textBaseline: baseline,
			font: font,
			text: getText(feature),
			fill: new ol.style.Fill({color: fillColor}),
			stroke: new ol.style.Stroke({color: outlineColor, width: outlineWidth}),
			offsetX: offsetX,
			offsetY: offsetY,
			rotation: rotation
		});
	};
	
	function getStyle (featureType) {
		var markerColor = 'blue';
		if (featureType == 'daily') {
			markerColor = 'red';
		}
		return new ol.style.Style({
			image: new ol.style.Circle({
				radius: 7,
				snapToPixel: false,
				fill: new ol.style.Fill({color: markerColor}),
				stroke: new ol.style.Stroke({
					color: 'white', width: 1
				})
			})
		})
	};
	function transformToFeature (item, type) {
		var feature = new ol.Feature({
			type: 'icon',
			geometry: new ol.geom.Point(ol.proj.fromLonLat([ item[2], item[1]])),
			name: ''+ item[0] + ''
		});
		feature.setStyle(getStyle(type));
		return feature;
	}
	var stationsFeatures = [];
	var simpleMeteoStations = [];
	_.each(uurStations, function(station){
		stationsFeatures.push(transformToFeature(station, 'hourly'));
		simpleMeteoStations.push(station);
	});
	_.each(dagStations, function(station){
		stationsFeatures.push(transformToFeature(station, 'daily'));
		simpleMeteoStations.push(station);
	});
	var source = new ol.source.Vector({wrapX: false});
	
	var vectorLayer = new ol.layer.Vector({
		id: 'vectorLayer',
		source: new ol.source.Vector({
			features: stationsFeatures,
			
		}),
		wrapX: false
	});
	<?php if($tab == 'aanmelden' || $tab == 'feedback') : ?>
		if(!readCookie('basicInfoFormData')){
			removeMeteostationsCookie();
		}
		
		var map = new ol.Map({
			target: 'map',
			layers: [
				new ol.layer.Tile({
					source: new ol.source.OSM()
				}),
				vectorLayer
			],
			view: new ol.View({
				center: ol.proj.fromLonLat([5, 52.3]),
				zoom: 8
			})
		});
	<?php endif; ?>
	// Map customisation for basisgegevens
	<?php if ($tab == 'basisgegevens') : ?>
		
		if(!readCookie('basicInfoFormData')){
			removeMeteostationsCookie();
		}
	
		var map = new ol.Map({
			target: 'map',
			layers: [
				new ol.layer.Tile({
					source: new ol.source.OSM()
				}),
				vectorLayer
			],
			view: new ol.View({
				center: ol.proj.fromLonLat([5, 52.3]),
				zoom: 8
			})
		});
		function disableFeature(feature) {
			var value = $('input[name="stationsType"]:checked').val();
			var markerColor = '';
			if (value == 'Uurstations') {
				markerColor = 'blue';
			} else {
				markerColor = 'red';
			}
			feature.setStyle(new ol.style.Style({
				image: new ol.style.Circle({
					radius: 7,
					snapToPixel: false,
					fill: new ol.style.Fill({color: markerColor}),
					stroke: new ol.style.Stroke({
						color: 'white', width: 1
					})
				})
			}));
		}
		function enableFeature(feature) {
			feature.setStyle(new ol.style.Style({
				image: new ol.style.Circle({
					radius: 7,
					snapToPixel: false,
					fill: new ol.style.Fill({color: 'green'}),
					stroke: new ol.style.Stroke({
						color: 'white', width: 1
					})
				}),
				text: createTextStyle(feature)
			}));
		}
		var selectInteraction = new ol.interaction.Select({
			layers: function (layer) {
				return layer.get('id') == 'vectorLayer';
			},
			condition: ol.events.condition.click
		});
		map.getInteractions().extend([selectInteraction]);
		function getStationNumber(name) {
			var value = $('input[name="stationsType"]:checked').val();
			var stationNumber;
			if (value == 'Uurstations') {
				_.each(hourlyStationsNumbers, function (station) {
					if (_.contains(station,name)) {
						stationNumber = station[1];
					}
				})
			} else if (value == 'Dagstations') {
				_.each(dagStationsNumbers, function (station) {
					
					if (_.contains(station,name)) {
						stationNumber = station[1];
					}
				})
			} else {
				mbAlert('Selecteer het type stations!', 'alert-danger');
				
			}
			return stationNumber;
		}
		
		function selectStation(features, deselected) {
			_.each(features, function (feature) {
				if(getStationNumber(feature.get('name'))) {
					createCookie('ms['+ feature.get('name') +']', getStationNumber(feature.get('name')));
					enableFeature(feature);
				}
				
			})
			_.each(deselected, function (feature) {
				eraseCookie('ms['+ feature.get('name') +']');
				disableFeature(feature);
			})
		}
		
		selectInteraction.addEventListener('select',function(evt) {
			var selected = evt.selected;
			var deselected = evt.deselected;
			selectStation(selected, deselected);
		});
		
	<?php endif; ?>
	$('input[name="stationsType"]').click(function () {
		var stationsType = this.value;
		if(!readCookie('basicInfoFormData')){
			removeMeteostationsCookie();
		}
		if (stationsType == 'Uurstations') {
			vectorLayer.get('source').clear();
			selectInteraction.getFeatures().clear();
			var stationsFeatures = [];
			_.each(uurStations, function(station){
				stationsFeatures.push(transformToFeature(station, 'hourly'));
			});
			$('input[type="checkbox"]#verdamping').parent().fadeIn();
			vectorLayer.get('source').addFeatures(stationsFeatures);
		} else {
			vectorLayer.get('source').clear();
			selectInteraction.getFeatures().clear();
			var stationsFeatures = [];
			_.each(dagStations, function(station){
				stationsFeatures.push(transformToFeature(station, 'daily'));
			});
			$('input[type="checkbox"]#verdamping').parent().fadeOut();
			vectorLayer.get('source').addFeatures(stationsFeatures);
			
		}
	})
	<?php if ($tab == 'rasterdata') : ?>
		
		var raster = new ol.layer.Tile({
			source: new ol.source.OSM()
		});
	
		var source = new ol.source.Vector({
			wrapX: false
		});
		<?php if (isset($_COOKIE['crdphp'])) : ?>
	
			minXY = [parseFloat(readCookie('minX')), parseFloat(readCookie('minY'))];
			maxXY = [parseFloat(readCookie('maxX')), parseFloat(readCookie('maxY'))];
			var transformedMinXY = ol.proj.transform(minXY, 'EPSG:28992', 'EPSG:3857');
			var transformedMaxXY = ol.proj.transform(maxXY, 'EPSG:28992', 'EPSG:3857');
			var feature = new ol.Feature({
				type: 'Polygon',
				geometry: ol.geom.Polygon.fromExtent([
					transformedMinXY[0],
					transformedMinXY[1],
					transformedMaxXY[0],
					transformedMaxXY[1]
				])
				
			});
			source.addFeature(feature);
		<?php endif; ?>
		var vector = new ol.layer.Vector({
			source: source
		});
 		var map = new ol.Map({
			layers: [raster, vector],
			target: 'map',
			view: new ol.View({
				center: ol.proj.fromLonLat([5, 52.3]),
				zoom: 8
			})
		});
		//	var typeSelect = document.getElementById('type');
		var draw; // global so we can remove it later
		
		function addInteraction() {
			var value = 'Circle';
			draw = new ol.interaction.Draw({
				source: source,
				type: /** @type {ol.geom.GeometryType} */ (value),
				geometryFunction: ol.interaction.Draw.createBox()
			});
			
			draw.on('drawend', function(evt){
				source.clear();
				var feature = evt.feature;
				var p = feature.getGeometry();
				var minX = p.getExtent()[0];
				var minY = p.getExtent()[1];
				var maxX = p.getExtent()[2];
				var maxY = p.getExtent()[3];
				minXY = [minX, minY];
				maxXY = [maxX, maxY];
				var transformedMinXY = ol.proj.transform(minXY, 'EPSG:3857', 'EPSG:28992');
				var transformedMaxXY = ol.proj.transform(maxXY, 'EPSG:3857', 'EPSG:28992');
				createCookie('minX', transformedMinXY[0]);
				createCookie('minY', transformedMinXY[1]);
				createCookie('maxX', transformedMaxXY[0]);
				createCookie('maxY', transformedMaxXY[1]);
				$('input#zwx').val(transformedMinXY[0]);
				$('input#zwy').val(transformedMinXY[1]);
				$('input#nox').val(transformedMaxXY[0]);
				$('input#noy').val(transformedMaxXY[1]);
				var recCoords = transformedMinXY+','+transformedMaxXY;
				createCookie('crdphp',recCoords);
			});
			
			map.addInteraction(draw);
		}
		
		addInteraction();
		$('#fileType').on('change', function (e) {
			if ($(this).val() == 'csv' || $(this).val() == 'sobek') {
				$('.fileUpload').removeClass('hidden').fadeIn();
			} else {
				$('.fileUpload').fadeOut();
			}
		})
		$(".upload-files-wrapper").dropzone({
			url: "<?php echo($_SERVER["PHP_SELF"]); ?>?tb=rasterDataUpload",
			maxFilesize: 20,
			dictDefaultMessage: "Drop your files here",
			success: function ($status, $data) {
				var text = $status.name;
				createCookie('ZIPFILE', text);
				$('#fileUploaded').val(text);
				mbAlert('Uw bestand is geüpload', 'alert-success');
			},
			acceptedFiles: '.zip',
			error: function () {
				mbAlert('Upload een geldige zip-file.', 'alert-danger');
			},
			init: function () {
				this.on('sending', function () {
					$("#uploading-status").show();
				});
				this.on("addedfile", function() {
				  if (this.files[1]!=null){
					this.removeFile(this.files[0]);
				  }
				});
				this.on('complete', function () {
					$('.upload-files-wrapper .default-text').hide();
					$('#uploading-success').show().delay(1500).slideUp();
				});
			}
		});
	
	<?php endif; ?>
	
	
	var legend = document.getElementById('legend');
	if (legend) {
		legend.style.display = 'none';
	}
	<?php if ($tab == 'rasterview') : ?>
		var rasterViewImage = '';

		<?php if ($_SESSION['rasterViewImage']) : ?>
			rasterViewImage = '<?php echo $_SESSION["rasterViewImage"];?>';
			legend.style.display = 'block';
		<?php unset($_SESSION['rasterViewImage']) ; ?>
		<?php endif; ?>
		
		var raster = new ol.layer.Tile({
			source: new ol.source.OSM()
		});
	
		var source = new ol.source.Vector({
			wrapX: false
		});
		var vector = new ol.layer.Vector({
			source: source
		});
		var imageExtent = [10727, 304275, 280729, 625291];
 		setTimeout(function () {
			var map = new ol.Map({
				layers: [raster, vector, new ol.layer.Image({
				source: new ol.source.ImageStatic({
				  url: '../images/rasterviews/' + rasterViewImage,
				  crossOrigin: '',
				  projection: 'EPSG:28992',
				  imageExtent: imageExtent
				}),
				opacity: 0.6
			  })],
				target: 'map',
				view: new ol.View({
				  center: ol.proj.transform(
					  ol.extent.getCenter(imageExtent), 'EPSG:28992', 'EPSG:3857'),
				  zoom: 8
				})
			});
		}, 1000);
		
	
	<?php endif; ?>
	<?php if ($tab == 'satdata') : ?>
		var rasterViewImage = '';

		<?php if ($_SESSION['rasterViewImage']) : ?>
			rasterViewImage = '<?php echo $_SESSION["rasterViewImage"];?>';
			legend.style.display = 'block';
		<?php unset($_SESSION['rasterViewImage']) ; ?>
		<?php endif; ?>
		
		var raster = new ol.layer.Tile({
			source: new ol.source.OSM()
		});
	
		var source = new ol.source.Vector({
			wrapX: false
		});
		var vector = new ol.layer.Vector({
			source: source
		});
		var imageExtent = [10727, 304275, 280729, 625291];
 		setTimeout(function () {
			var map = new ol.Map({
				layers: [raster, vector, new ol.layer.Image({
				source: new ol.source.ImageStatic({
				  url: '../images/rasterviews/' + rasterViewImage,
				  crossOrigin: '',
				  projection: 'EPSG:28992',
				  imageExtent: imageExtent
				}),
				opacity: 0.6
			  })],
				target: 'map',
				view: new ol.View({
				  center: ol.proj.transform(
					  ol.extent.getCenter(imageExtent), 'EPSG:28992', 'EPSG:3857'),
				  zoom: 8
				})
			});
		}, 1000);
		
	
	<?php endif; ?>	<?php if ($tab == 'rasterHarmonie') : ?>
		var rasterHarmonieImage = '';

		<?php if ($_SESSION['rasterHarmonieImage']) : ?>
			rasterHarmonieImage = '<?php echo $_SESSION["rasterHarmonieImage"];?>';
			//legend.style.display = 'block';
		<?php unset($_SESSION['rasterHarmonieImage']) ; ?>
		<?php endif; ?>
		
		var raster = new ol.layer.Tile({
			source: new ol.source.OSM()
		});
	
		var source = new ol.source.Vector({
			wrapX: false
		});
		var vector = new ol.layer.Vector({
			source: source
		});
		var imageExtent = [10727, 304275, 280729, 625291];
 		setTimeout(function () {
			var map = new ol.Map({
				layers: [raster, vector, new ol.layer.Image({
				source: new ol.source.ImageStatic({
				  url: '../images/rasterviews/' + rasterHarmonieImage,
				  crossOrigin: '',
				  projection: 'EPSG:28992',
				  imageExtent: imageExtent
				}),
				opacity: 0.6
			  })],
				target: 'map',
				view: new ol.View({
				  center: ol.proj.transform(
					  ol.extent.getCenter(imageExtent), 'EPSG:28992', 'EPSG:3857'),
				  zoom: 8
				})
			});
		}, 1000);
		
	
	<?php endif; ?>
	<?php if ($tab == 'toetsingsdata') : ?>
	features = [];
	_.each(polygons, function (polygon) {
		var ring = [];
		_.each(polygon.points, function (point) {
			var longLat = ol.proj.fromLonLat([point[1], point[0]]);
			ring.push(longLat);
		});
		var feature = new ol.Feature({
			type: 'Feature',
			geometry: new ol.geom.Polygon([ring]),
			name: ''+ polygon.name + '',
			
		});
		feature.setStyle(
			new ol.style.Style({
				stroke: new ol.style.Stroke({
					color: ''+polygon.strokeCollor+'',
					width: 3
				}),
				fill: new ol.style.Fill({
					color: ''+polygon.strokeCollor+''
				})
			})
		)
//			source.addFeature(feature);
		features.push(feature);
	});
	
	var vectorLayer = new ol.layer.Vector({
		source: new ol.source.Vector({
			features: features
		})
	});
	
	var map = new ol.Map({
		target: 'map',
		layers: [
			new ol.layer.Tile({
				source:  new ol.source.OSM()
			}),
			vectorLayer
		],
		view: new ol.View({
			center: ol.proj.fromLonLat([5, 52.3]),
			zoom: 8
		})
	});
	<?php endif;?>
	
	let geomapout = document.getElementById('zoom-out');
	if (geomapout) {
		geomapout.onclick = function() {
			var view = map.getView();
			var zoom = view.getZoom();
			view.setZoom(zoom - 1);
		};
	}
	
	let geomapin = document.getElementById('zoom-in');
	if (geomapin) {
		geomapin.onclick = function() {
		var view = map.getView();
		var zoom = view.getZoom();
		view.setZoom(zoom + 1);
	};
	}
		$.stellar({
			horizontalScrolling: false,
			verticalOffset: 0,
			responsive: true
		});
		$('.head-container').stellar();
		$('#features-slider').slick({
			'prevArrow': '.features-slider .arrows .prev',
			'nextArrow': '.features-slider .arrows .next'
		});
		$("li[data-toggle='tooltip']").tooltip();
		$('.modal-opener').click(function (e) {
			e.preventDefault();
			e.stopPropagation();
			var modalTarget = $(this).data('target');
			$(modalTarget).modal();
		});
		$('#rasterDataTabs a').click(function (e) {
			e.preventDefault();
			$(this).tab('show');
		});
		$('#registerModal .submit-btn').click(function () {
			$("#registerModalForm").submit();
		});
		$('#basicInfoForm .submit-btn').click(function (e) {
			e.stopPropagation();
			e.preventDefault();
			
			if (checkMeteostationsCookie()) {
				if(!_.isEmpty(readCookie('gebruiker[naam]'))) {
					if(readCookie('basicInfoFormData')) {
						eraseCookie('basicInfoFormData');
					}
					$("#basicInfoForm").submit();
				} else {
					createCookie('redirect_after_login', '?tb=basisgegevens&dp=basisgegevens');
					createCookie('basicInfoFormData', $('#basicInfoForm').serialize());
					$("#registerModal").modal();
				}
			} else {
				mbAlert('Selecteer een of meer stations op de kaart!', 'alert-danger');
			}
		});
		$('#rasterViewForm .submit-btn').click(function (e) {
			e.stopPropagation();
			e.preventDefault();
			
			if(!_.isEmpty(readCookie('gebruiker[naam]'))) {
				if(readCookie('rasterViewFormData')) {
					eraseCookie('rasterViewFormData');
				} else {
					createCookie('rasterViewFormData', $('#rasterViewForm').serialize());
					$("#rasterViewForm").submit();
				}
			} else {
				createCookie('redirect_after_login', '?tb=rasterview&dp=rasterview');
				createCookie('rasterViewFormData', $('#rasterViewForm').serialize());
				$("#registerModal").modal();
			}
			
		});
		
		$('#rasterHarmonieForm .submit-btn').click(function (e) {
			e.stopPropagation();
			e.preventDefault();
			
			if(!_.isEmpty(readCookie('gebruiker[naam]'))) {
				if(readCookie('rasterHarmonieFormData')) {
					eraseCookie('rasterHarmonieFormData');
				} 
				createCookie('rasterHarmonieFormData', $('#rasterHarmonieForm').serialize());
				$("#rasterHarmonieForm").submit();
			} else {
				createCookie('redirect_after_login', '?tb=rasterHarmonie&dp=rasterHarmonie');
				createCookie('rasterHarmonieFormData', $('#rasterHarmonieForm').serialize());
				$("#registerModal").modal();
			}
		});

		$('#rasterDataForm .submit-btn').click(function (e) {
			e.stopPropagation();
			e.preventDefault();
			if(!_.isEmpty(readCookie('gebruiker[naam]'))) {
				if ($('#rasterDataForm #fileType').val() == 'csv' || $('#rasterDataForm #fileType').val() == 'sobek') {
					if (!_.isEmpty($('#veldnaam').val())) {
						if (!_.isEmpty($('#fileUploaded').val())) {	
							if(readCookie('rasterDataFormData')) {
								eraseCookie('rasterDataFormData');
							}
							$('#rasterDataForm').submit();
						} else {
							mbAlert('Upload een geldige zip-file.', 'alert-danger');
						}
					} else {
						e.stopPropagation();
						e.preventDefault();
						mbAlert('Voer uw veldnaam in!', 'alert-danger');
					}
				} else {
					if(!_.isEmpty(readCookie('crdphp'))){
						if(readCookie('rasterDataFormData')) {
							eraseCookie('rasterDataFormData');
						}
						$('#rasterDataForm').submit();
					} else {
						e.stopPropagation();
						e.preventDefault();
						mbAlert('Selecteer een gebied op de kaart!', 'alert-danger');
					}
				}
			} else {
				createCookie('redirect_after_login', '?tb=rasterdata&dp=rasterdata&dp_sub=introductie');
				createCookie('rasterDataFormData', $('#rasterDataForm').serialize());
				$("#registerModal").modal();
			}
		});
		
		$('#polygonModal').on('click', '.submit-btn', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var validator = $('#polygonCoordinates').valid();
			
			if (validator) {
				$("#polygonCoordinates").submit();
				createCookie('minX', $('input#zwx').val());
				createCookie('minY', $('input#zwy').val());
				createCookie('maxX', $('input#nox').val());
				createCookie('maxY', $('input#noy').val());
				minXY = [parseFloat(readCookie('minX')), parseFloat(readCookie('minY'))];
				maxXY = [parseFloat(readCookie('maxX')), parseFloat(readCookie('maxY'))];
				var transformedMinXY = ol.proj.transform(minXY, 'EPSG:28992', 'EPSG:3857');
				var transformedMaxXY = ol.proj.transform(maxXY, 'EPSG:28992', 'EPSG:3857');
				var recCoords = transformedMinXY+','+transformedMaxXY;
				createCookie('crdphp',recCoords);

				var feature = new ol.Feature({
					type: 'Polygon',
					geometry: ol.geom.Polygon.fromExtent([
						transformedMinXY[0],
						transformedMinXY[1],
						transformedMaxXY[0],
						transformedMaxXY[1]
					])
					
				});
				source.clear();
				source.addFeature(feature);
				$(this).closest('.modal').modal('hide');
			} else {
				return false;
			}
			
		});
		
		$('#testingInfoForm .submit-btn').click(function (e) {
			e.stopPropagation();
			e.preventDefault();
			if(!_.isEmpty(readCookie('gebruiker[naam]'))) {
				if(readCookie('testingInfoFormData')) {
					eraseCookie('testingInfoFormData');
				}
				$("#testingInfoForm").submit();
			} else {
				createCookie('redirect_after_login', '?tb=toetsingsdata&dp=toetsingsdata');
				createCookie('testingInfoFormData', $('#testingInfoForm').serialize());
				$("#registerModal").modal();
			}
		});

		$('#feedbackModal .submit-btn').click(function () {
			$("#feedbackModalForm").submit();
		});
		$('.disabled-menu-link').click(function (e) {
			e.preventDefault();
			mbAlert('U dient zich eerst te registreren.', 'alert-warning');
		});
		$('.mobile-menu-toggle').click(function (e) {
			e.preventDefault();
			$('.mobile-menu').toggleClass('active-menu');
		})
		
		<?php if ($tab != 'rasterview') : ?>
		$('#dateFrom').datepicker({
			dateFormat: 'dd/mm/yy',
			onSelect: function () {
				$('#dateTo').valid();
			},
			onChange: function () {
				$('#dateTo').valid();
			}
		});
		$('#dateTo').datepicker({
			dateFormat: 'dd/mm/yy',
			onSelect: function () {
				$('#dateFrom').valid();
			},
			onChange: function () {
				$('#dateTo').valid();
			}
		});
		<?php endif; ?>
		$('#rasterHarmonieDateFrom').datepicker({
			dateFormat: 'dd/mm/yy',
			onSelect: function () {
				$('#rasterHarmonieDateFrom').valid();
			},
			onChange: function () {
				$('#rasterHarmonieDateFrom').valid();
			}
		});
		
		$('#rasterViewDateFrom').datepicker({
			dateFormat: 'dd/mm/yy',
			onSelect: function () {
				$('#rasterViewDateFrom').valid();
			},
			onChange: function () {
				$('#rasterViewDateFrom').valid();
			}
		});
		
		$('#dateFrom').datepicker('setDate', '01/01/1951');
		$('#dateTo').datepicker('setDate', '31/12/2016');
		$('#rasterViewDateFrom').datepicker('setDate', '15/01/1990');
		$('#rasterHarmonieDateFrom').datepicker('setDate', '01/01/2016');

		<?php if ($tab == 'rasterdata') : ?>
		$('#dateFrom').datepicker('setDate', '01/01/1990');
		$('#dateTo').datepicker('setDate', '31/12/2016');
		<?php endif;?>
		<?php if ($tab == 'rasterview') : ?>
			$( "#slider-interval" ).slider({
			  range: true,
			  min: 0,
			  max: 15,
			  values: [ 0, 1 ],
			  slide: function( event, ui ) {
				$("#interval-start").val(ui.values[ 0 ])
				$("#interval-end").val(ui.values[ 1 ])
				//var textInterval = ui.values[ 0 ] + " " + ((ui.values[ 0 ] == 1 || ui.values[ 0 ] == -1) ? 'dag' : 'dagen') + " tot " + ui.values[ 1 ] + " " + ((ui.values[ 1 ] == 1 || ui.values[ 1 ] == -1) ? 'dag' : 'dagen');
				var textInterval = ui.values[ 0 ] + ' tot ' + ui.values[ 1 ] +' dagen na gekozen datum (' + 24*(ui.values[ 1 ] - ui.values[ 0 ]) + ' uur)';
				$("#interval").text(textInterval);
			  }
			});
		<?php endif;?>
		$('.checkbox-list').hide();
		var typeButton = ('input[name="dataType"]');
		$(typeButton).click(function (e) {
			var currentValue = $(this).val();
			$('.checkbox-list').hide();
			$('#'+ currentValue + '-checkboxes' ).removeClass('hidden').fadeIn();
		});
		function parseDate(input) {
			var parts = input.match(/(\d+)/g);
			// note parts[1]-1
			return new Date(parts[2], parts[1]-1, parts[0]);
		}
		
		jQuery.validator.addMethod("basicInfoMinDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrAfter(moment('01/01/1951', 'DD/MM/YYYY'));
		}, "De begindatum mag niet eerder dan 01/01/1951 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("rasterHarmonieMinDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrAfter(moment('01/01/2014', 'DD/MM/YYYY'));
		}, "De begindatum mag niet eerder dan 01/01/2014 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("herhalingMinDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrAfter(moment('15/01/1990', 'DD/MM/YYYY'));
		}, "De begindatum mag niet eerder dan 15/01/1990 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("dateRangeCheck", function(value, element) {
			if (moment($('#dateTo').val(), 'DD/MM/YYYY').isBefore(moment($('#dateFrom').val(), 'DD/MM/YYYY'))) {
				return false;
			}
			return this.optional(element) || true;
		}, "De startdatum moet voor de einddatum liggen.");
		
		jQuery.validator.addMethod("basicInfoMaxDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrBefore(moment('31/12/2050', 'DD/MM/YYYY'));
		}, "De einddatum mag niet later dan  31/12/2050 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("rasterDataMinDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrAfter(moment('01/01/1990', 'DD/MM/YYYY'));
		}, "De begindatum mag niet eerder dan 01/01/1990 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("rasterDataMaxDate", function(value, element) {
			return this.optional(element) || moment(value, 'DD/MM/YYYY').isSameOrBefore(moment('31/12/2050', 'DD/MM/YYYY'));;
		}, "De einddatum mag niet later dan 31/12/2050 zijn. De datum moet de notatie DD/MM/JJJJ hebben.");
		
		jQuery.validator.addMethod("dateRange", function (value, element) {
			if (this.optional(element)) {
				return true;
			}
			
			if($("#fileType").val() != 'sobek' && $("#fileType").val() != 'csv') {
				if ($(element).attr('id') == 'dateFrom') {
					if (!$('#dateTo').val()) {
						return false;
					} 
					var startDate = moment(value, 'DD/MM/YYYY');
					var endDate = moment($('#dateTo').val(), 'DD/MM/YYYY');
					
				} else if ($(element).attr('id') == 'dateTo') {
					if (!$('#dateFrom').val()) {
						return false;
					}
					var startDate = moment($('#dateFrom').val(), 'DD/MM/YYYY'),
					endDate = moment(value, 'DD/MM/YYYY');
				}
				
				var months = endDate.diff(startDate, 'months');
				return months <= 24 ? true : false;
			} else {
				return true;
			}
			
		}, 'Maximaal 2 jaar per bestelling is toegestaan');
		
		$('#basicInfoForm').validate({
			rules: {
				fromDate: {
					required: true,
					basicInfoMinDate: true,
					basicInfoMaxDate: true,
					dateRangeCheck: true
				},
				toDate: {
					required: true,
					basicInfoMaxDate: true,
					basicInfoMinDate: true,
					dateRangeCheck: true
				},
				stationsType: {
					required: true
				}
			},
			submitHandler: function(form) {
				
				var fromDateFormatted = moment($("#basicInfoForm input[name=fromDate]").val(), 'DD/MM/YYYY').format('YYYYMMDD');
				var toDateFormatted = moment($("#basicInfoForm input[name=toDate]").val(), 'DD/MM/YYYY').format('YYYYMMDD');

				$("#basicInfoForm input[name=fromDate]").val(fromDateFormatted);
				$("#basicInfoForm input[name=toDate]").val(toDateFormatted);
				form.submit();
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$("#chartTabs").tabs({
			classes: {
				"ui-tabs": "ui-corner-all",
				"ui-tabs-nav": "nav nav-pills mb-3",
				"ui-tabs-tab": "nav-item",
				"ui-tabs-panel": "ui-corner-bottom"
			}
        });
		$('#rasterViewForm').validate({
			rules: {
				rasterViewDateFrom: {
					required: true,
					herhalingMinDate: true,
					rasterDataMaxDate: true
				},
			},
			submitHandler: function(form) {
				var fromDate = moment($("#rasterViewForm input[name=rasterViewDateFrom]").val(), 'DD/MM/YYYY');
				var intervalStart = $("#rasterViewForm input[name=interval-start]").val();
				var intervalEnd = $("#rasterViewForm input[name=interval-end]").val();
				var fromDateFormatted = moment(fromDate).add(parseInt(intervalStart), 'days').format('YYYYMMDD');
				var toDateFormatted = moment(fromDate).add(parseInt(intervalEnd), 'days').format('YYYYMMDD');
				var fileName = _.uniqueId(moment().format('YYYYMMDDHHmmSS'));
				$("#rasterViewForm input[name=intervalStartDate]").val(fromDateFormatted);
				$("#rasterViewForm input[name=intervalEndDate]").val(toDateFormatted);
				$("#rasterViewForm input[name=fileName]").val(fileName);
				form.submit();
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$('#rasterHarmonieForm').validate({
			rules: {
				rasterHarmonieDateFrom: {
					required: true,
					rasterHarmonieMinDate: true,
					rasterDataMaxDate: true
				},
			},
			submitHandler: function(form) {
				var fromDate = moment($("#rasterHarmonieForm input[name=rasterHarmonieDateFrom]").val(), 'DD/MM/YYYY');
				var fromDateFormatted = moment(fromDate).format('YYYYMMDD');
				var fileName = _.uniqueId(moment().format('YYYYMMDDHHmmSS'));
				$("#rasterHarmonieForm input[name=intervalStartDate]").val(fromDateFormatted);
				$("#rasterHarmonieForm input[name=fileName]").val(fileName);
				form.submit();
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$('#polygonCoordinates').validate({
			rules: {
				zwx: {
					required: true,
					range: [-1000, 280000]
				},
				zwy: {
					required: true,
					range: [300000, 650000]
				},
				nox: {
					required: true,
					range: [-1000, 280000]
				},
				noy: {
					required: true,
					range: [300000, 650000]
				}
			},
			messages: {
				zwx: {
					required: "Voer uw min x in",
					range: "Waarde moet tussen -1000 en 280000 liggen."
				},
				zwy: {
					required: "Voer uw min y in",
					range: "Waarde moet tussen 300000 en 650000 liggen."
				},
				nox: {
					required: "Voer uw max in",
					range: "Waarde moet tussen -1000 en 280000 liggen."
				},
				noy: {
					required: "Voer uw max y in",
					range: "Waarde moet tussen 300000 en 650000 liggen."
				},
			},
			submitHandler: function(form) {
				form.submit();
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			onfocusout: function(element) { $(element).valid(); },
			onkeyup: function(element) { $(element).valid(); },
			onclick: function(element) { $(element).valid(); },
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$('#rasterDataForm').validate({
			rules: {
				fromDate: {
					required: true,
					rasterDataMinDate: true,
					dateRange: true,
					rasterDataMaxDate: true,
					dateRangeCheck: true
				},
				toDate: {
					required: true,
					rasterDataMaxDate: true,
					rasterDataMinDate: true,
					dateRange: true,
					dateRangeCheck: true
				},
				fileType: {
					required: true
				}
			},
			submitHandler: function(form) {
				form.submit();
			},
			onfocusout: function(element) { $(element).valid(); },
			onkeyup: function(element) { $(element).valid(); },
			onclick: function(element) { $(element).valid(); },
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent().parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label").parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$("#registerModalForm").validate({
			rules: {
				userFullName: {
					required: true,
					minlength: 2
				},
				userEmail: {
					required: true,
					email: true
				},
				userCompany: {
					required: true,
					minlength: 3
				}
			},
			messages: {
				userFullName: {
					required: "Voer uw naam in",
					minlength: "Uw naam moet uit ten minste 2 karakters bestaan"
				},
				userCompany: {
					required: "Voer uw bedrijfsnaam in",
					minlength: "Uw bedrijfsnaam moet uit ten minste 3 karakters bestaan"
				},
				userEmail: "Voer een geldig e-mailadres in"
			},
			submitHandler: function(form) {
				form.submit();
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		$('#dateTo').change(function(e){
			$(this).valid();
			$('#dateFrom').valid();
		});
		$('#dateFrom').change(function(e){
			$(this).valid();
			$('#dateTo').valid();
		});
		$("#feedbackModalForm").validate({
			rules: {
				userFullNameFeedback: {
					required: true,
					minlength: 2
				},
				userEmailFeedback: {
					required: true,
					email: true
				},
				feedbackMessage: {
					required: true,
					minlength: 10
				}
			},
			submitHandler: function(form) {
				form.submit();
			},
			messages: {
				userFullNameFeedback: {
					required: "Voer uw naam in",
					minlength: "Uw naam moet uit ten minste 2 karakters bestaan"
				},
				feedbackMessage: {
					required: "Voer uw bericht in",
					minlength: "Uw bericht moet uit ten minste 10 karakters bestaan."
				},
				userEmailFeedback: "Voer een geldig e-mailadres in"
			},
			errorElement: "em",
			errorPlacement: function (error, element) {
				
				// Add the `help-block` class to the error element
				error.addClass("help-block");
				if(element.parent('.input-group').length) {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element.parent());
					}
					
				} else {
					if (element.prop("type") === "checkbox") {
						error.insertAfter(element.parent("label"));
					} else {
						error.insertAfter(element);
					}
				}
				
			},
			highlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-error").removeClass("has-success");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).closest(".form-group").addClass("has-success").removeClass("has-error");
			}
		});
		
		if ($('#rasterDataForm').length) {
			var rasterData = readCookie('rasterDataFormData');
			rasterData.split('&').forEach(function(keyValuePair) { // not necessarily the best way to parse cookies
				var cookieName = keyValuePair.replace(/=.*$/, ""); // some decoding is probably necessary
				var cookieValue = keyValuePair.replace(/^[^=]*\=/, ""); // some decoding is probably necessary
				if (cookieName == 'fileType') {
					$('#rasterDataForm #fileType').val(cookieValue);
					$('#rasterDataForm #fileType').trigger('change');
				} else if (cookieName == 'makkink' || cookieName == 'neerslag' || cookieName == 'penman' || cookieName == 'evtact' || cookieName == 'evtsho') {
					$('#rasterDataForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').attr('checked', 'true');
				} else {
					$('#rasterDataForm input[name="' + cookieName + '"]').val(cookieValue);
				}
			});
			if(!_.isEmpty(rasterData)) {
				$('#rasterDataForm .submit-btn').trigger('click');
			}
		}
		if ($('#basicInfoForm').length) {
			var basicInfoData = readCookie('basicInfoFormData')
			basicInfoData.split('&').forEach(function(keyValuePair) { // not necessarily the best way to parse cookies
				var cookieName = keyValuePair.replace(/=.*$/, ""); // some decoding is probably necessary
				var cookieValue = keyValuePair.replace(/^[^=]*\=/, ""); // some decoding is probably necessary
				if (cookieName == 'stationsType') {
					$('#basicInfoForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').attr('checked', 'true');
					$('#basicInfoForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').trigger('click');
				} else if (cookieName == 'makkink' || cookieName == 'penman' || cookieName == 'neerslag' || cookieName == 'evtact' || cookieName == 'evtsho') {
					$('#basicInfoForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').attr('checked', 'true');
				} else {
					$('#basicInfoForm input[name="' + cookieName + '"]').val(cookieValue);
				}
			});
			if(!_.isEmpty(basicInfoData)) {
				$('#basicInfoForm .submit-btn').trigger('click');
			}
		}
		if ($('#rasterHarmonieForm').length) {
			var rasterHarmonieData = readCookie('rasterHarmonieFormData');
			rasterHarmonieData.split('&').forEach(function(keyValuePair) { // not necessarily the best way to parse cookies
				var cookieName = keyValuePair.replace(/=.*$/, ""); // some decoding is probably necessary
				var cookieValue = keyValuePair.replace(/^[^=]*\=/, ""); // some decoding is probably necessary
				if (cookieName == 'harmonieTijd' || cookieName == 'voorspellingshorizon') {
					$("#rasterHarmonieForm #" + cookieName).val(cookieValue);
					$("#rasterHarmonieForm #" + cookieName).trigger('change');
				} else if (cookieName == 'gegevenstype') {
					$('#rasterHarmonieForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').attr('checked', 'true');
				} else {
					$('#rasterHarmonieForm input[name="' + cookieName + '"]').val(cookieValue);
				}
			});
			if(!_.isEmpty(rasterData)) {
				$('#rasterHarmonieForm .submit-btn').trigger('click');
			}
		}
		if ($('#rasterViewForm').length) {
			var rasterViewData = readCookie('rasterViewFormData');
			if (!_.isEmpty(rasterViewData)){
				var rasterViewCookie = {};
				rasterViewData.split('&').forEach(function(keyValuePair) { // not necessarily the best way to parse cookies
					var cookieName = keyValuePair.replace(/=.*$/, ""); // some decoding is probably necessary
					var cookieValue = keyValuePair.replace(/^[^=]*\=/, ""); // some decoding is probably necessary
					rasterViewCookie[cookieName] = cookieValue;
				});
				$('#rasterViewDateFrom').val(rasterViewCookie['rasterViewDateFrom'] || '15/01/1990');
				var rasterViewStart = rasterViewCookie['interval-start'];
				var rasterViewEnd = rasterViewCookie['interval-end'];
				$('#interval-start').val(rasterViewStart || '15/01/1990');
				$('#interval-end').val(rasterViewEnd || '15/01/1990');
				//var intervalText = (rasterViewStart || 0) + " " + ((rasterViewStart == 1 || rasterViewStart == -1) ? 'dag' : 'dagen') + " tot " + (rasterViewEnd || 15) + " " + ((rasterViewEnd == 1 || rasterViewEnd == -1) ? 'dag' : 'dagen');
				//var intervalText = (rasterViewStart || 0) + " " + ((rasterViewStart == 1 || rasterViewStart == -1) ? 'dag' : 'dagen') + " tot " + (rasterViewEnd || 15) + " " + ((rasterViewEnd == 1 || rasterViewEnd == -1) ? 'dag' : 'dagen');
				$( "#interval" ).text(intervalText);
				$( "#slider-interval" ).slider({
				  range: true,
				  min: 0,
				  max: 15,
				  values: [ rasterViewStart || 0, rasterViewEnd || 15 ],
				  slide: function( event, ui ) {
					$("#interval-start").val(ui.values[ 0 ]);
					$("#interval-end").val(ui.values[ 1 ]);
					//var textValue = ui.values[ 0 ] + " " + ((ui.values[ 0 ] == 1 || ui.values[ 0 ] == -1) ? 'dag' : 'dagen') + " tot " + ui.values[ 1 ] + " " + ((ui.values[ 1 ] == 1 || ui.values[ 1 ] == -1) ? 'dag' : 'dagen');
					var textValue = ui.values[ 0 ] + ' tot ' + ui.values[ 1 ] + ' dagen na gekozen datum (' + 24*(ui.values[1] - ui.values[0]) + ' uur)';
					$( "#interval" ).text(textValue);
				  }
				});
				$('#rasterViewForm .submit-btn').trigger('click');
			}
		}
		if ($('#testingInfoForm').length) {
			var testingInfoForm = readCookie('testingInfoFormData')
			testingInfoForm.split('&').forEach(function(keyValuePair) { // not necessarily the best way to parse cookies
				var cookieName = keyValuePair.replace(/=.*$/, ""); // some decoding is probably necessary
				var cookieValue = keyValuePair.replace(/^[^=]*\=/, ""); // some decoding is probably necessary
				if (cookieName == 'dataType') {
					$('#testingInfoForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').trigger('click');
				} else {
					$('#testingInfoForm input[name="' + cookieName + '"][value="' + cookieValue + '"]').attr('checked', 'true');
				}
			});
			if(!_.isEmpty(testingInfoForm)) {
				$('#testingInfoForm .submit-btn').trigger('click');
			}
		}
		<?php if(isset($_SESSION['feedbackMsg'])): ?>
			mbAlert('<?php echo $_SESSION["feedbackMsg"]; ?>', 'alert-success');
		<?php unset($_SESSION['feedbackMsg']) ; ?>
		<?php endif;?>
		<?php if(isset($_SESSION['dataFeedbackMsg'])): ?>
			var message = '' + 'Uw bestelling met sessie-ID ';
			message = message + readCookie('gebruiker[sessionid]');
			message = message  + ' is in goede orde ontvangen en wordt op dit moment verwerkt. <br>';
			message = message  + ' Zodra hij is afgehandeld, wordt hij klaargezet op de <a href="https://www.meteobase.nl/meteobase/downloads/">downloadpagina</a>. <br>';
			message = message  + ' U ontvang een e-mail met een rechtstreekse download-link.';
			$('#alert_placeholder').html(
			'<div class="alert alert-success">'+message+'</div>'
			);
			$('#alert_placeholder').fadeIn();
			setTimeout(function () {
				$('#alert_placeholder').fadeOut();
			}, 3000);
			<?php unset($_SESSION['dataFeedbackMsg']) ; ?>
		<?php endif;?>
		
		<?php if(isset($_SESSION['errorMsg'])): ?>
			mbAlert('<?php echo $_SESSION["errorMsg"]; ?>', 'alert-danger');
			<?php unset($_SESSION['errorMsg']) ; ?>
		<?php endif;?>
	});
	

</script>

</body>
</html>