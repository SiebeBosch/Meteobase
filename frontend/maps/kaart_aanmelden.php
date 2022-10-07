<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">

function initialize() {
	var latlng = new google.maps.LatLng(52.10000, 5.29524);
	var myOptions = {
		zoom: 7,
		center: latlng,
		disableDefaultUI: true,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	setMarkers(map, gegevensMarker);
}

<?php
include('mstations_coords.php');
?>
var image = new google.maps.MarkerImage('images/BallBlue.png', new google.maps.Size(30,30), new google.maps.Point(0,0), new google.maps.Point(15,15));

function setMarkers(map, locations) {
	for(var i = 0; i < locations.length; i++) {
		var gegevensMarker = locations[i];
		var myLatLng = new google.maps.LatLng(gegevensMarker[1], gegevensMarker[2]);
		var marker = new google.maps.Marker({
			position: myLatLng,
			map: map,
			icon: image,
			title: gegevensMarker[0]
		});
	}
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