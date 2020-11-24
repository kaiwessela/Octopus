class Post {
	constructor() {
		this.id;
		this.longid;
		this.overline;
		this.headline;
		this.subline;
		this.teaser;
		this.author;
		this.timestamp;
		this.image;
		this.content;

		this.is_new;
		this.is_empty;


		this.is_new = false;
		this.is_empty = true;
	}

	pull(identifier) {
		return new Promise((resolve, reject) => {
			if(!this.is_empty){
				throw 'Post.pull(): Post is not empty.';
			}

			if(!identifier instanceof String || identifier.length < 1){
				throw 'Post.pull(): invalid identifier.';
			}

			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					if(ajax.response.response_code == '200 OK'){
						this.load(ajax.response.result);
						resolve();
					} else {
						reject(ajax.response.error_message);
					}
				}
			}
			ajax.open('GET', '/api/v1/posts/' + identifier, true);
			ajax.send();
		});
	}

	static pullList(limit = null, offset = null) {
		return new Promise((resolve, reject) => {
			var ajax = new XMLHttpRequest();
			ajax.responseType = 'json';
			ajax.onreadystatechange = () => {
				if(ajax.readyState == 4){
					console.log(ajax);
					if(ajax.response.response_code == '200 OK'){
						var results = [];
						ajax.response.result.forEach((data) => {
							var post = new Post();
							post.load(data);
							results.push(post);
						});
						resolve(results);
					} else {
						reject(ajax.response.error_message);
					}
				}
			}

			if(Number.isInteger(limit) && limit > 0){
				if(Number.isInteger(offset) && offset > 0){
					ajax.open('GET', '/api/v1/posts?limit=' + limit + '&offset=' + offset, true);
				} else {
					ajax.open('GET', '/api/v1/posts?limit=' + limit, true);
				}
			} else {
				ajax.open('GET', '/api/v1/posts/', true);
			}

			ajax.send();
		});
	}

	load(data) {
		if(!this.is_empty){
			throw 'Post.load(): Post is not empty.';
		}

		this.id = data.id;
		this.longid = data.longid;
		this.overline = data.overline;
		this.headline = data.headline;
		this.subline = data.subline;
		this.teaser = data.teaser;
		this.author = data.author;
		this.timestamp = data.timestamp;
		// this.image = new Image();
		// this.image.load(data.image);
		this.content = data.content;

		this.is_new = false;
		this.is_empty = false;
	}

	replace(string) {
		string = string.replace(/{{id}}/g, this.id);
		string = string.replace(/{{longid}}/g, this.longid);
		string = string.replace(/{{overline}}/g, this.overline);
		string = string.replace(/{{headline}}/g, this.headline);
		string = string.replace(/{{subline}}/g, this.subline);
		string = string.replace(/{{teaser}}/g, this.teaser);
		string = string.replace(/{{author}}/g, this.author);
		string = string.replace(/{{timestamp}}/g, this.timestamp);
		string = string.replace(/{{content}}/g, this.content);
		return string;
	}
}
