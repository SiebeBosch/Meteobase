<?php if(isset($_COOKIE['ms'])){ $mstations = $_COOKIE['ms']; } ?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
var nederland = new google.maps.LatLng(52.10000, 5.29524);

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
		zoom: 7,
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

	var infowindow = new google.maps.InfoWindow(); 
	
	var marker, i; 
	var locations = gegevensMarker;

	for (i = 0; i < locations.length; i++) {   
		marker = new google.maps.Marker({ 
		position: new google.maps.LatLng(locations[i][1], locations[i][2]), 
		map: map,
		icon: new google.maps.MarkerImage(locations[i][3], new google.maps.Size(20,20), new google.maps.Point(0,0), new google.maps.Point(15,15)),
		title: locations[i][0],
		zIndex: 1
	}); 
	
	google.maps.event.addListener(marker, 'click', (function(marker, i) {
		return function() {
			var colorImage = marker.getIcon().url;
			if(colorImage == 'images/BallYellow.png'){
				var tekstdel = 'ms['+locations[i][0]+']';
				eraseCookie(tekstdel);
				var selectedImage = new google.maps.MarkerImage('images/BallBlue.png', new google.maps.Size(20,20), new google.maps.Point(0,0), new google.maps.Point(15,15)); 
				marker.setIcon(selectedImage);
				marker.setZIndex(1);
				//Siebe: let op: bij deselecteren moet de OORSPRONKELIJKE kleur terugkomen
			}else{
				var selectedImage = new google.maps.MarkerImage('images/BallYellow.png', new google.maps.Size(20,20), new google.maps.Point(0,0), new google.maps.Point(15,15)); 
				marker.setIcon(selectedImage);
				var tekst = 'ms['+locations[i][0]+']';
				createCookie(tekst);
				marker.setZIndex(2);				
			}

		}
		})(marker, i));
	}

	polygon_bilt.setMap(map);
	polygon_mild1.setMap(map);
	polygon_mild2.setMap(map);
	polygon_mild3.setMap(map);
	polygon_mild4.setMap(map);
	polygon_mild5.setMap(map);
	polygon_mild6.setMap(map);
	polygon_hevig1.setMap(map);
	polygon_hevig2.setMap(map);
	polygon_hevig3.setMap(map);
	polygon_zhevig1.setMap(map);
	polygon_zhevig2.setMap(map);
}
var polygon_bilt;
var polygon_mild1;
var polygon_mild2;
var polygon_mild3;
var polygon_mild4;
var polygon_mild5;
var polygon_mild6;
var polygon_hevig1;
var polygon_hevig2;
var polygon_hevig3;
var polygon_zhevig1;
var polygon_zhevig2;
<?php 
//include('polygons_coords.php');
include('mstations_coords.php');
?>

polygon_bilt = new google.maps.Polygon({
	paths: biltCoords,
	strokeColor: "#F87217",
	strokeOpacity: 0.1,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.15
});

polygon_mild1 = new google.maps.Polygon({
	paths: mild1Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.5
});

polygon_mild2 = new google.maps.Polygon({
	paths: mild2Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

polygon_mild3 = new google.maps.Polygon({
	paths: mild3Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

polygon_mild4 = new google.maps.Polygon({
	paths: mild4Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

polygon_mild5 = new google.maps.Polygon({
	paths: mild5Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

polygon_mild6 = new google.maps.Polygon({
	paths: mild6Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

polygon_hevig1 = new google.maps.Polygon({
	paths: hevig1Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

polygon_hevig2 = new google.maps.Polygon({
	paths: hevig2Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

polygon_hevig3 = new google.maps.Polygon({
	paths: hevig3Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

polygon_zhevig1 = new google.maps.Polygon({
	paths: zhevig1Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.5
});

polygon_zhevig2 = new google.maps.Polygon({
	paths: zhevig2Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.5
});

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