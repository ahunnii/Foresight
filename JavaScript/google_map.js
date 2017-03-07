function initializeMapWithAddress(address, node) {
	var mapOptions = {
	  zoom: 8,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	var map = new google.maps.Map((node != null) ? node : document.getElementById("map_canvas"), mapOptions);
	
	var geocoder = new google.maps.Geocoder();
	
	geocoder.geocode( { 'address': address }, 
		function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				map.setCenter(results[0].geometry.location);
				var marker = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location
				});
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
		}
	);
}