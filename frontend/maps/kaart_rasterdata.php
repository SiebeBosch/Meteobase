<?php
// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
// Meteobase
// Version 6-7
if(isset($_COOKIE['coordinaat'])){
	$coordinaat = $_COOKIE['coordinaat'];
	$coordinaat0 = $coordinaat['0'];	 
	$coordinaat1 = $coordinaat['1'];
	$coordinaat2 = $coordinaat['2'];
	$coordinaat3 = $coordinaat['3'];
}else{
	$coordinaat0 = '50.65001'; 
	$coordinaat1 = '3.06582';
	$coordinaat2 = '53.55560';
	$coordinaat3 = '7.36133';	
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
function createCookie(name,value,days) { 
        if (days) { 
                var date = new Date(); 
                date.setTime(date.getTime()+(days*24*60*60*1000)); 
                var expires = "; expires="+date.toGMTString(); 
        } 
        else var expires = ""; 
        document.cookie = name+"="+value+expires+"; path=/"; 
} 

var map;
var nederland = new google.maps.LatLng(52.10000, 5.29524);

function HomeControl(controlDiv, map) {
	controlDiv.style.padding = '5px';

	var controlUI = document.createElement('DIV');
	controlUI.style.backgroundColor = 'white';
	controlUI.style.borderStyle = 'solid';
	controlUI.style.borderWidth = '2px';
	controlUI.style.cursor = 'pointer';
	controlUI.style.textAlign = 'center';
	controlUI.title = 'Klik hier voor de beginwaarde.';
	controlDiv.appendChild(controlUI);
	
	var controlText = document.createElement('DIV');
	controlText.style.fontFamily = 'Arial,sans-serif';
	controlText.style.fontSize = '12px';
	controlText.style.paddingLeft = '4px';
	controlText.style.paddingRight = '4px';
	controlText.innerHTML = 'Zoom beginwaarde';
	controlUI.appendChild(controlText);
	
	google.maps.event.addDomListener(controlUI, 'click', function() { map.setCenter(nederland), map.setZoom(7) });
}

function initialize() {
	var latlng = new google.maps.LatLng(52.10000, 5.29524);
	var myOptions = {
		zoom: 6,
		center: latlng,
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL	
		},
		mapTypeControl: false,
		streetViewControl: false,
		mapTypeId: google.maps.MapTypeId.TERRAIN
	};
		
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	
	var homeControlDiv = document.createElement('DIV');
	var homeControl = new HomeControl(homeControlDiv, map);
	homeControlDiv.index = 1;
	map.controls[google.maps.ControlPosition.TOP_CENTER].push(homeControlDiv);

<?php 
echo "
var bounds = new google.maps.LatLngBounds(
	new google.maps.LatLng(" . $coordinaat0 . ", " . $coordinaat1 . "),
	new google.maps.LatLng(" . $coordinaat2 . ", " . $coordinaat3 . ")
);";
?>

var rectOptions = {
bounds: bounds,
map: map,
clickable: true,
editable: true
};
var rectangle = new google.maps.Rectangle(rectOptions);

google.maps.event.addListener(rectangle, 'bounds_changed', function() {  
	var coords = rectangle.getBounds();
	createCookie('crdphp',coords);
});

}

</script>

<style type="text/css">
html { height: 100% }
body { height: 100%; margin: 0; padding: 0 }
#map_canvas { height: 100% }
</style>

</head>

<body onload="initialize()">

	<div id="map_canvas" style="width:100%; height:100%"></div>

</body>
</html>