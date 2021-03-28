function µ(name) {
	switch (name) {
		case 'DataObject': return new DataObject();
		case 'Image': return new Image();
		case 'Application': return new Application();
		case 'Person': return new Person();
		case 'Group': return new Group();
		case 'Post': return new Post();
		case 'Column': return new Column();
		case 'Modal': return new Modal();
		case 'SelectModal': return new SelectModal();
		case 'UploadModal': return new UploadModal();
		case 'Pagination': return new Pagination({}, 0);
		default: return {};
	}
}
