// JavaScript Document
var map;
var centerMap = new google.maps.LatLng(52.15000, 5.22524);
var stationArray = [];

function createCookie(name,value,days) { 
        if (days) { 
                var date = new Date(); 
                date.setTime(date.getTime()+(days*24*60*60*1000)); 
                var expires = "; expires="+date.toGMTString(); 
        } 
        else var expires = ""; 
        document.cookie = name+"="+value+expires+"; path=/"; 
} 

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function eraseStations() {
	var cookies = { };
	if (document.cookie && document.cookie != '') {
		var split = document.cookie.split(';');
		for (var i = 0; i < split.length; i++) {
			var name_value = split[i].split("=");
			name_value[0] = name_value[0].replace(/^ /, '');
			cookies[decodeURIComponent(name_value[0])] = decodeURIComponent(name_value[1]);
		}
	}
	for(var name in cookies) {
		if(!name.search("ms")) {
			eraseCookie(name);
		}
	}
}

function wgs2rd(lat,lon) {
	x0      = 155000;
	y0      = 463000;
	k       = 0.9999079;
	bigr    = 6382644.571;
	m       = 0.003773954;
	n       = 1.000475857;
	lambda0 = 0.094032038;
	phi0    = 0.910296727;
	l0      = 0.094032038;
	b0      = 0.909684757;
	e       = 0.081696831;
	a       = 6377397.155;
	
	// wgs84 to bessel
	dphi = lat - 52;
	dlam = lon - 5;

	phicor = ( -96.862 - dphi * 11.714 - dlam * 0.125 ) * 0.00001;
	lamcor = ( dphi * 0.329 - 37.902 - dlam * 14.667 ) * 0.00001;

	phibes = lat - phicor;
	lambes = lon - lamcor;

	phi			= phibes / 180 * Math.PI;
	lambda		= lambes / 180 * Math.PI;
	qprime		= Math.log( Math.tan( phi / 2 + Math.PI / 4 ));
	dq			= e / 2 * Math.log(( e * Math.sin(phi) + 1 ) / ( 1 - e * Math.sin( phi ) ) );
	q			= qprime - dq;
		
	w			= n * q + m;
	b			= Math.atan( Math.exp( w ) ) * 2 - Math.PI / 2;
	dl			= n * ( lambda - lambda0 );

	d_1			= Math.sin( ( b - b0 ) / 2 );
	d_2			= Math.sin( dl / 2 );
	
	s2psihalf	= d_1 * d_1 + d_2 * d_2 * Math.cos( b ) * Math.cos ( b0 );
	cpsihalf	= Math.sqrt( 1 - s2psihalf );
	spsihalf	= Math.sqrt( s2psihalf );
	tpsihalf	= spsihalf / cpsihalf;
	
	spsi		= spsihalf * 2 * cpsihalf;
	cpsi		= 1 - s2psihalf * 2;
	
	sa			= Math.sin( dl ) * Math.cos( b ) / spsi;
	ca			= ( Math.sin( b ) - Math.sin( b0 ) * cpsi ) / ( Math.cos( b0 ) * spsi );
	
	r			= k * 2 * bigr * tpsihalf;
	x			= r * sa + x0;
	y			= r * ca + y0;
	
	var coords = new Array(2);
	coords[0] = Math.round(x);
	coords[1] = Math.round(y);
	return coords;
}

function rd2wgs(rdX,rdY){
	// This calculation was based from the sourcecode of Ejo Schrama's software <schrama@geo.tudelft.nl>.
	// You can find his software on: http://www.xs4all.nl/~digirini/contents/gps.html

	// Fixed constants / coefficients
	var x0      = 155000;
	var y0      = 463000;
	var k       = 0.9999079;
	var bigr    = 6382644.571;
	var m       = 0.003773954;
	var n       = 1.000475857;
	var lambda0 = 0.094032038;
	var phi0    = 0.910296727;
	var l0      = 0.094032038;
	var b0      = 0.909684757;
	var e       = 0.081696831;
	var a       = 6377397.155;

	// Convert RD to Bessel

	// Get radius from origin.
	d_1 = rdX - x0;
	d_2 = rdY - y0;
	r   = Math.sqrt( Math.pow(d_1, 2) + Math.pow(d_2, 2) );  // Pythagoras

	// Get Math.sin/Math.cos of the angle
	sa  = (r != 0 ? d_1 / r : 0);  // the if prevents devision by zero.
	ca  = (r != 0 ? d_2 / r : 0);

	psi  = Math.atan2(r, k * 2 * bigr) * 2;   // php does (y,x), excel does (x,y)
	cpsi = Math.cos(psi);
	spsi = Math.sin(psi);

	sb = (ca * Math.cos(b0) * spsi) + (Math.sin(b0) * cpsi);
	d_1 = sb;
	
	cb = Math.sqrt(1 - Math.pow(d_1, 2));  // = Math.cos(b)
	b  = Math.acos(cb);
	
	sdl = sa * spsi / cb;  // = Math.sin(dl)
	dl  = Math.asin(sdl);         // delta-lambda

	lambda_1 = dl / n + lambda0;
	w        = Math.log(Math.tan((b / 2) + (Math.PI / 4)));
	q        = (w - m) / n;

	// Create first phi and delta-q
	phiprime = (Math.atan(Math.exp(q)) * 2) - (Math.PI / 2);
	dq_1     = (e / 2) * Math.log((e * Math.sin(phiprime) + 1) / (1 - e * Math.sin(phiprime)));
	phi_1    = (Math.atan(Math.exp(q + dq_1)) * 2) - (Math.PI / 2);

	// Create new phi with delta-q
	dq_2     = (e / 2) * Math.log((e * Math.sin(phi_1) + 1) / (1 - e * Math.sin(phi_1)));
	phi_2    = (Math.atan(Math.exp(q + dq_2)) * 2) - (Math.PI / 2);

	// and again..
	dq_3     = (e / 2) * Math.log((e * Math.sin(phi_2) + 1) / (1 - e * Math.sin(phi_2)));
	phi_3    = (Math.atan(Math.exp(q + dq_3)) * 2) - (Math.PI / 2);
	
	// and again...
	dq_4     = (e / 2) * Math.log((e * Math.sin(phi_3) + 1) / (1 - e * Math.sin(phi_3)));
	phi_4    = (Math.atan(Math.exp(q + dq_4)) * 2) - (Math.PI / 2);

	// radians to degrees
	lambda_2 = lambda_1 / Math.PI * 180;  // 
	phi_5    = phi_4    / Math.PI * 180;


	// Bessel to wgs84 (lat/lon)
	dphi   = phi_5    - 52;   // delta-phi
	dlam   = lambda_2 -  5;   // delta-lambda
	
	phicor = (-96.862 - (dphi * 11.714) - (dlam * 0.125)) * 0.00001; // correction factor?
	lamcor = ((dphi * 0.329) - 37.902 - (dlam * 14.667))  * 0.00001;
	
	phiwgs = phi_5    + phicor;
	lamwgs = lambda_2 + lamcor;


	// Return as anonymous object
	var coords = new Array(2);
	coords[0] = phiwgs;
	coords[1] = lamwgs;
	return coords;
}

HomeControl.prototype.home_ = null;

HomeControl.prototype.getHome = function() {
	return this.home_;
}

HomeControl.prototype.setHome = function(home) {
	this.home_ = home;
}

function HomeControl(map, div, home) {
	var controlDiv = div;  
	var control = this;  
	control.home_ = home;  
	controlDiv.style.padding = '7px'; 

	var goHomeUI = document.createElement('div');
	goHomeUI.style.backgroundColor = 'white';
	goHomeUI.style.borderStyle = 'solid';
	goHomeUI.style.borderWidth = '2px';
	goHomeUI.style.cursor = 'pointer';
	goHomeUI.style.textAlign = 'center';
	goHomeUI.title = 'Klik hier voor de beginwaarde.';
	controlDiv.appendChild(goHomeUI);

	var goHomeText = document.createElement('div');
	goHomeText.style.fontFamily = 'Arial, Helvetica, sans-serif';
	goHomeText.style.fontSize = '12px';
	goHomeText.style.paddingLeft = '4px';
	goHomeText.style.paddingRight = '4px';
	goHomeText.innerHTML = 'Zoom beginwaarde';
	goHomeUI.appendChild(goHomeText);

	if (typeof recBounds != 'undefined') {
		var setHomeUI = document.createElement('div');
		setHomeUI.style.backgroundColor = 'white';
		setHomeUI.style.borderStyle = 'solid';
		setHomeUI.style.borderWidth = '2px';
		setHomeUI.style.cursor = 'pointer';
		setHomeUI.style.textAlign = 'center';
		setHomeUI.title = 'Klik hier voor de rasterwaarde.';
		controlDiv.appendChild(setHomeUI);
	
		var setHomeText = document.createElement('div');
		setHomeText.style.fontFamily = 'Arial, Helvetica, sans-serif';
		setHomeText.style.fontSize = '12px';
		setHomeText.style.paddingLeft = '4px';
		setHomeText.style.paddingRight = '4px';
		setHomeText.innerHTML = 'Zoom rasterwaarde';
		setHomeUI.appendChild(setHomeText);

		google.maps.event.addDomListener(setHomeUI, 'click', function() {
			if(readCookie('crdphp')) {
				var coordinaten = readCookie('crdphp');
				coordinaten = coordinaten.split(",");
				var coordsSouthwest = rd2wgs(coordinaten[0],coordinaten[1]);
				var coordsNortheast = rd2wgs(coordinaten[2],coordinaten[3]);
				map.fitBounds(new google.maps.LatLngBounds(
					new google.maps.LatLng(parseFloat(coordsSouthwest[0]),parseFloat(coordsSouthwest[1])),
					new google.maps.LatLng(parseFloat(coordsNortheast[0]),parseFloat(coordsNortheast[1]))
				));
			}else{
				map.setCenter(centerMap);
				map.setZoom(7);
			}
			
		});
	}
	
google.maps.event.addDomListener(goHomeUI, 'click', function() {
		var currentHome = control.getHome();
		map.setCenter(centerMap);
		map.setZoom(7);
	});

}

function initialize() {
	var optionMap = {
		center: centerMap,
		zoom: 7,
		zoomControl: true,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL	
		},
		mapTypeControl: false,
		streetViewControl: false,
		MapTypeId: google.maps.MapTypeId.TERRAIN
	};

	map = new google.maps.Map(document.getElementById('kaartenpaneel'), optionMap);

	var cityLabels = [
		{
			featureType: "administrative",
			elementType: "labels",
			stylers: [
					{ visibility: "off" }
			]
		},
		{
			featureType: "road",
			elementType: "labels",
			stylers: [
					{ visibility: "off" }
			]
		}
	];
	map.setOptions({styles: cityLabels});

	var homeControlDiv = document.createElement('div');
	var homeControl = new HomeControl(map, homeControlDiv, centerMap);
	homeControlDiv.index = 1;
	map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);

	if (typeof biltCoords != 'undefined') { 
		// Initieren polygonen
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
	
	if (typeof recBounds != 'undefined') {
		if(readCookie('crdphp')) {
			var coordinaten = readCookie('crdphp');
			coordinaten = coordinaten.split(",");
			
			document.getElementById('SW01').value = coordinaten[0];
			document.getElementById('SW02').value = coordinaten[1];
			document.getElementById('NE01').value = coordinaten[2];
			document.getElementById('NE02').value = coordinaten[3];
			
			var coordsSouthwest = rd2wgs(coordinaten[0],coordinaten[1]);
			var coordsNortheast = rd2wgs(coordinaten[2],coordinaten[3]);			
			
			recBounds = new google.maps.LatLngBounds(
				new google.maps.LatLng(parseFloat(coordsSouthwest[0]),parseFloat(coordsSouthwest[1])),
				new google.maps.LatLng(parseFloat(coordsNortheast[0]),parseFloat(coordsNortheast[1]))
			);
				
		}
		
		var rectOptions = {
			bounds: recBounds,
			map: map,
			clickable: true,
			editable: true
		};

		// Initieren raster
		var rectangle = new google.maps.Rectangle(rectOptions);

		google.maps.event.addListener(rectangle, 'bounds_changed', function() {  
			var recCoords = rectangle.getBounds();
			var gmCoordsNE = recCoords.getNorthEast();
			var gmCoordsSW = recCoords.getSouthWest();
			
			var coordsSW = wgs2rd(gmCoordsSW.lat(),gmCoordsSW.lng());
			var coordsNE = wgs2rd(gmCoordsNE.lat(),gmCoordsNE.lng());
			
			recCoords = coordsSW+','+coordsNE;
			createCookie('crdphp',recCoords);
			
			document.getElementById('SW01').value = coordsSW[0];
			document.getElementById('SW02').value = coordsSW[1];
			document.getElementById('NE01').value = coordsNE[0];
			document.getElementById('NE02').value = coordsNE[1];
		});
		
		var myButton = document.getElementById('coordinaten_aanpassen');
		
		google.maps.event.addDomListener(myButton, 'click', function() {
			var coordsNE = new Array();
			var coordsSW = new Array();
			
			coordsSW[0] = parseInt(document.getElementById('SW01').value);
			coordsSW[1] = parseInt(document.getElementById('SW02').value);
			coordsNE[0] = parseInt(document.getElementById('NE01').value);
			coordsNE[1] = parseInt(document.getElementById('NE02').value);
			
			if(coordsSW[0] > coordsSW[1] || coordsNE[0] > coordsNE[1] || coordsSW[0] < 0 || coordsSW[1] < 300000 || coordsNE[0] > 300000 || coordsNE[1] > 630000) {
				alert('Foutmelding: De door u ingevulde coordinaten zijn verkeerd of vallen buiten Nederland.');
				
				var recCoords = rectangle.getBounds();
				var gmCoordsNE = recCoords.getNorthEast();
				var gmCoordsSW = recCoords.getSouthWest();
				
				var coordsSW_rd = wgs2rd(gmCoordsSW.lat(),gmCoordsSW.lng());
				var coordsNE_rd = wgs2rd(gmCoordsNE.lat(),gmCoordsNE.lng());
				
				document.getElementById('SW01').value = coordsSW_rd[0];
				document.getElementById('SW02').value = coordsSW_rd[1];
				document.getElementById('NE01').value = coordsNE_rd[0];
				document.getElementById('NE02').value = coordsNE_rd[1];
			}else{
				var coordsSW_wgs = rd2wgs(coordsSW[0],coordsSW[1]);
				var coordsNE_wgs = rd2wgs(coordsNE[0],coordsNE[1]);

				recCoords = coordsSW_wgs+','+coordsNE_wgs;
				createCookie('crdphp',recCoords);
			
				recBounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(parseFloat(coordsSW_wgs[0]),parseFloat(coordsSW_wgs[1])),
					new google.maps.LatLng(parseFloat(coordsNE_wgs[0]),parseFloat(coordsNE_wgs[1]))
				);

				rectangle.setBounds(recBounds);
				
				var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng(parseFloat(coordsSW_wgs[0]),parseFloat(coordsSW_wgs[1])),
					new google.maps.LatLng(parseFloat(coordsNE_wgs[0]),parseFloat(coordsNE_wgs[1]))
				);
				map.fitBounds(bounds);
			}
		});
	}

	// Selectiefunctie aanmelden of basisgegevens
	for(var i = 0; i < locations.length; i++) {
		var coords = new google.maps.LatLng(locations[i][1],locations[i][2]);
		addMarkerBlocked(coords, locations[i][3], locations[i][0]);
	}
}
	
function deleteOverlays() {
	if (stationArray) {
		for (i in stationArray) {
			stationArray[i].setMap(null);
		}
		stationArray.length = 0;
	}
}

var marker, i;

function addMarker(positieMarker, imageMarker, titleMarker) {
	marker = new google.maps.Marker({
		position: positieMarker,
		map: map,
		icon: imageMarker,
		title: titleMarker,
		zIndex: 1,
		kleur: imageMarker,
		naam: titleMarker,
		boolean: false
	});
	stationArray.push(marker);

	google.maps.event.addListener(marker, "click", function () {
		var colorImage = this.kleur;
		var bool = this.boolean;
		// dagstations selecteren
		if(colorImage == 'images/DropletBlue3.png' && bool === false) {
			this.setIcon('images/dropletYellow.png');
			this.boolean = true;
			this.setZIndex(2);
			var tekst = 'ms['+this.naam+']';
			createCookie(tekst);
		}
		// dagstations deselecteren
		if(colorImage == 'images/DropletBlue3.png' && bool === true) {
			this.setIcon('images/DropletBlue3.png');
			this.boolean = false;
			this.setZIndex(1);
			var tekstdel = 'ms['+this.naam+']';
			eraseCookie(tekstdel);
		}
		// uurstations selecteren
		if(colorImage == 'images/DropletPurple3.png' && bool === false) {
			this.setIcon('images/DropletGreen.png');
			this.boolean = true;
			this.setZIndex(2);
			var tekst = 'ms['+this.naam+']';
			createCookie(tekst)
		}
		// uurstations deselecteren
		if(colorImage == 'images/DropletPurple3.png' && bool === true) {
			this.setIcon('images/DropletPurple3.png');
			this.boolean = false;
			this.setZIndex(1);
			var tekstdel = 'ms['+this.naam+']';
			eraseCookie(tekstdel);
		}
	})
}

function addMarkerBlocked(positieMarker, imageMarker, titleMarker) {
	marker = new google.maps.Marker({
		position: positieMarker,
		map: map,
		icon: imageMarker,
		title: titleMarker,
	});
	stationArray.push(marker);	
}

function selectStation(waarde) {
	// Bestaande markers verwijderen
	if (stationArray != 0) {
        deleteOverlays();
	}
	
	// Cookie ms[] leegmaken
	eraseStations();

	// Selecteren uur- of dagstations
	if(waarde.value == 'uur') {
		locations = uurStations;
	}
	if(waarde.value == 'dag') {
		locations = dagStations;
	}
	// Nieuwe markers plaatsen
	for(var i = 0; i < locations.length; i++) {
		var coords = new google.maps.LatLng(locations[i][1],locations[i][2]);
		addMarker(coords, locations[i][3] ,locations[i][0]);
	}
}

var polygon_bilt = new google.maps.Polygon({
	paths: biltCoords,
	strokeColor: "#F87217",
	strokeOpacity: 0.1,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.15
});

var polygon_mild1 = new google.maps.Polygon({
	paths: mild1Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.5
});

var polygon_mild2 = new google.maps.Polygon({
	paths: mild2Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

var polygon_mild3 = new google.maps.Polygon({
	paths: mild3Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

var polygon_mild4 = new google.maps.Polygon({
	paths: mild4Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

var polygon_mild5 = new google.maps.Polygon({
	paths: mild5Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

var polygon_mild6 = new google.maps.Polygon({
	paths: mild6Coords,
	strokeColor: "#F87217",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#F87217",
	fillOpacity: 0.4
});

var polygon_hevig1 = new google.maps.Polygon({
	paths: hevig1Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

var polygon_hevig2 = new google.maps.Polygon({
	paths: hevig2Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

var polygon_hevig3 = new google.maps.Polygon({
	paths: hevig3Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.4
});

var polygon_zhevig1 = new google.maps.Polygon({
	paths: zhevig1Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.5
});

var polygon_zhevig2 = new google.maps.Polygon({
	paths: zhevig2Coords,
	strokeColor: "#3300CC",
	strokeOpacity: 0.7,
	strokeWeight: 1,
	fillColor: "#3300CC",
	fillOpacity: 0.5
});