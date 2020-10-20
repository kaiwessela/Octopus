class Image {
	constructor(data = null) {
		this.id;
		this.longid;
		this.extension;
		this.description;
		this.copyright;
		this.imagedata;

		if(data != null){
			this.id = data.id;
			this.longid = data.longid;
			this.extension = data.extension;
			this.description = data.description;
			this.copyright = data.copyright;
		}
	}
}
