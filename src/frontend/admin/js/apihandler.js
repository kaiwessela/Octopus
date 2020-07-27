class APIHandler {
	constructor(url, method = 'GET', post = null) {
		this.url;
		this.method;
		this.post;
		this.response;
		this.xhr;

		this.url = url;
		this.method = method;
		this.post = post;
		this.xhr = new XMLHttpRequest();
		this.xhr.onreadystatechange = this.readyStateChange(this);
	}

	fire() {
		this.xhr.open(this.method, this.url, true);
		this.xhr.send(this.post);
	}

	readyStateChange(that) {
		if(this.readyState == 4 && this.status == 200){
			that.response = this.responseText;
		}
	}
}
