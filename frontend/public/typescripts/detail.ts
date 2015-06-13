
/**
 * HTML loaded from the server
 */
interface LoadedData {
	[index: string]: string;
}

class Detail {
	
	static data: LoadedData = {};
	
	/**
	 * Fetch HTML of detail. 
	 */
	static fetch(id: string, callback: (html: string) => void) {
		if(this.data.hasOwnProperty(id)) {
			callback(this.data[id]);
		}
		
		// load it via 
		var req = new XMLHttpRequest();
		req.open("get", "/schools/get-detail/" + id, true);
		req.onload = (e: Event) => {
			this.data[id] = req.responseBody;
			callback(this.data[id]);
		};
	}
	
	public Render(params) {
		
	}
	
}