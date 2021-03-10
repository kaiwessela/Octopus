<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Media\Image;
use \Blog\Model\DataObjects\Lists\Media\ImageList;
use \Blog\Model\FileManager;

class ImageController extends Controller {
	const MODEL = Image::class;
	const LIST_MODEL = ImageList::class;

	public function scan() : ?array {
		if($this->request->action == 'multi'){
			$result = [];
			foreach($this->object->objects as $obj){
				$result[$obj->id] = FileManager::scan($obj);
			}
			return $result;
		} else if($this->request->action == 'show'){
			return FileManager::scan($this->object);
		} else {
			return null;
		}
	}
}
?>
