
var es = require("elasticsearch");
var client = new es.Client({
	host: "http://schools-hacks.cloudapp.net:8080"
});

var request = require("request");
var urlencode = require("urlencode");

var openStreetApiUrl = "http://nominatim.openstreetmap.org/search.php?q=";
function getLocation(address, callback) {
	var osm_request = openStreetApiUrl + urlencode(address) + "&format=json";
	console.log("open street maps request: ", osm_request);
	
	request({
		method: "GET",
		uri: osm_request		
	}, function(error, response, body) {
		if(error || body.length == 0) {
			callback({ success: false });
			return;
		}
		
		if(typeof(body) === "string") {
			body = JSON.parse(body);
		}
		
		var place = body[0];
		if(place && place.hasOwnProperty("lon")
			&& place.hasOwnProperty("lan")) {
			callback({
				success: true,
				location: {
					lon: place.lon,
					lat: place.lat				
				}
			});
			return;	
		}		
	
		// no data
		callback({ success: false });
		return;
	});
}


/**
 * Extracts the information about the schools and creates items array
 */
var Schools = (function() {
	
	function Schools (data) {
		this.items = [];
		
		// some response from the server?
		if(data !== undefined) {
			for(var i = 0; i < data.hits.hits.length; ++i) {
				this.items.push(new School(data.hits.hits[i]));
			}	
		}	
	}
	
	return Schools;
})();

/**
 * Extract important information of one school from the database structure.
 */
var School = (function() {
	
	function School(data) {
		if(data !== undefined) {
			this.id = data._source.id;
			this.general = data._source.general;
			this.food = data._source.food;
			this.teaching = data._source.teaching;	
		}		
	}
	
	return School;
	
})();


function parseQuery(data, prefix) {
	var res = "";
	for (var key in data) {
		if(data.hasOwnProperty(key)) {
			if(data[key].is)
			res += key + ":" + data[key] + "&";
		}
	}
	
	return res;
}

function queryElastic(setup, callback, location) {
	client.search(setup).then(function(response) {	
		// pass the queried data
		var schools;
		try {
			schools = new Schools(response);
			callback(schools, response.aggregations, location);
			return;					
		} catch (err) {
			console.log(err);
			schools = null;
		}
		
		callback(null, {});
	}, function(err) {
		// error - no data
		console.log("Elastic search error:", err);
		callback(new Schools(), {});
	});
}


module.exports = (function() {
	
	return {
		getAll: function(filter, address, callback) {
			var setup = {
				index: "schools",
				type: "school",
				body: {
					aggregations: {
						"languages": { "terms": { "field": "teaching.languages" } },
						"pe_hours_per_week": { "terms": { "field": "teaching.pe_hours_per_week" } }
					}
				}				
			};
			
			// create a query maybe..
			var q = parseQuery(filter);
			if(q.length > 0) {
				setup["q"] = q;
			}
			
			console.log("address: ", address);
			if(address) {	
				// Ask Open Street maps to do the job
				getLocation(address, function(res) {
					console.log("response: ", res);
					if(res.success === true) {
						setup.body.query = {
							filtered: {
								filter: {
									geo_distance: {
										distance: "3km",
										"general.position": res.location
									}							
								}
							}
						};						
					}
					
					queryElastic(setup, callback, res.location || undefined);																
				});
			} else {			
				queryElastic(setup, callback);				
			}
		},
				
		get: function(id, callback) {
			client.get({
				index: "schools",
				type: "school",
				id: id
			}).then(function(response) {
				var school;		
				try {
					school = new School(response);										
				} catch (err) {
					school = null;
				}	
				
				callback(school);
			}, function(err) {
				callback(null);
			});
		}
	}
	
})();

