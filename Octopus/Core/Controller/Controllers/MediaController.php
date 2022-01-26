<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controllers\DataObjectController;
use \Blog\Model\FileManager;

class MediaController extends DataObjectController {


	public function scan() : ?array {
		if($this->call->action == 'list'){
			$result = [];
			foreach($this->object->objects as $obj){
				$result[$obj->id] = FileManager::scan($obj);
			}
			return $result;
		} else if($this->call->action == 'show'){
			return FileManager::scan($this->object);
		} else {
			return null;
		}
	}


}
?>
