function GeoDistance() { }

GeoDistance.toMiles = function toMiles(km) {
	return km * 0.621371192;
};

GeoDistance.toRadians = function toRadians(degrees) {
	return degrees * 0.0174532925;
};

// Returns the distance in radians between two geographical coordinates using the Haversine formula
GeoDistance.kmDist = function kmDist(lat1, lon1, lat2, lon2) {
	lat1 = GeoDistance.toRadians(lat1);
	lon1 = GeoDistance.toRadians(lon1);
	lat2 = GeoDistance.toRadians(lat2);
	lon2 = GeoDistance.toRadians(lon2);
	
	var radius = 6371.01; //radius of earth in kilometers
	
	var result = 2.0 * radius * Math.asin(Math.sqrt(GeoDistance.haversin(lat1 - lat2) + Math.cos(lat1) * Math.cos(lat2) * GeoDistance.haversin(lon1 - lon2)));
	
	return result;
};

// Haversine function = sin^2(angle/2) = 1 - cos(angle) / 2
GeoDistance.haversin = function haversin(angle) {
	return (1.0 - Math.cos(angle)) / 2.0;
};

