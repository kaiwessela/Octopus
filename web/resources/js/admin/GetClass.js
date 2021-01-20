function µ(name) {
	switch (name) {
		case 'DataObject': return new DataObject();
		case 'Image': return new Image();
		case 'Person': return new Person();
		case 'Modal': return new Modal();
		case 'SelectModal': return new SelectModal();
		case 'UploadModal': return new UploadModal();
		case 'Pagination': return new Pagination({}, 0);
		default: return {};
	}
}
