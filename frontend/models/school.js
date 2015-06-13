

module.exports = (function() {
	
	return {
		getAll: function() {
			return [
				{
					id: 0,
					name: "ZŠ Horní Dolní",
					web: "http://www.zshornidolni.cz"	
				}
			];
		},
		
		get: function(id) {
			return {
				id: 0,
				name: "ZŠ Horní Dolní",
				web: "http://www.zshornidolni.cz"	
			};
		}
	}
	
})();