class Post extends DataObject {
	constructor() {
		super();

		this.overline;
		this.headline;
		this.subline;
		this.teaser;
		this.author;
		this.timestamp;
		this.content;

		super._type = 'Post';
		super._apiname = 'posts';
		super.properties = [
			'id',
			'longid',
			'overline',
			'headline',
			'subline',
			'teaser',
			'author',
			'timestamp',
			'content'
		];
	}

	extractFromElement(elem) {
		return new Promise((resolve, reject) => {
			if(!elem instanceof HTMLElement){
				throw 'Group.extractFromElement(): elem is not an HTMLElement.';
			}

			for(var i = 0; i < elem.children.length; i++){
				if(this.properties.includes(elem.children[i].name)){
					if(elem.children[i].name == 'id'){
						this.id = elem.children[i].value;
						this.isNew = false;
					} else {
						this[elem.children[i].name] = elem.children[i].value;
					}
				}
			}

			this.isEmpty = false;

			resolve();
		});
	}
}
