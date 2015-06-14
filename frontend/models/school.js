
var es = require("elasticsearch");
var client = new es.Client({
	host: "http://schools-hacks.cloudapp.net:8080"
});

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


module.exports = (function() {
	
	return {
		getAll: function(filter, callback, location) {
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
			
			if(location) {
				setup.body.filtered = {
					filter: {
						geo_distance: {
							distance: "200km",
							"general.position": {
								"lat": location.lat,
								"loc": location.loc
							}
						}							
					}
				};
			}
			
			// create a query maybe..
			var q = parseQuery(filter);
			if(q.length > 0) {
				setup["q"] = q;
			}
			
			client.search(setup).then(function(response) {	
				// pass the queried data				
				var schools;
				try {
					schools = new Schools(response);
					callback(schools, response.aggregations);
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

