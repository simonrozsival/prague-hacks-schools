
var request = require("request");

//var url = "http://private-anon-6fe701970-praguehacksschools.apiary-mock.com/api";
var url = "http://schools-hacks.cloudapp.net/api";

/**
 * See http://docs.praguehacksschools.apiary.io/#
 */

module.exports = (function() {
	
	function execute(options, callback) {
		request(options, function(err, res, body) {
			if(typeof(body) == "string") {
				body = JSON.parse(body);
			}
			
			console.log("req body:", body);				
			if(err || !body.hasOwnProperty("success") || body.success === false) {
				callback(false);
			} else {
				callback(true);
			}
		});
	}
	
	return {		
		subscribe: function(schoolId, email, callback) {
			var options = {
				url: url + "/subscribe",
				method: "POST",
				form: {
					"school_id": schoolId,
					"email": email
				}
			}
			
			console.log(options);
			execute(options, callback);
		},
		
		unsubscribe: function(schoolId, email, token, callback) {			
			var options = {
				uri: url + "/unsubscribe",
				method: "POST",
				form: {
					"school_id": schoolId,
					"email": email,
					"cancellation_token": token
				}
			}
			
			execute(options, callback);
		},
				
		requestEdit: function(schoolId, email, callback) {			
			var options = {
				uri: url + "/request-edit",
				method: "POST",
				form: {
					"school_id": schoolId,
					"email": email
				}
			}
			
			execute(options, callback);
		},
		
		claimOwnership: function(schoolId, email, message, callback) {			
			var options = {
				uri: url + "/claim-ownership",
				method: "POST",
				form: {
					"school_id": schoolId,
					"email": email,
					"message": message
				}
			}
			
			execute(options, callback);
		},
		
		editSchoolInformation: function(schoolId, token, data, callback) {			
			var options = {
				uri: url + "/school/" + schoolId + "/edit/" + token,
				method: "POST",
				form: data
			}
			
			execute(options, callback);
		},
	}
	
})();