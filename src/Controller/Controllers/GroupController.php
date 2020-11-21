<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;

class GroupController extends Controller {
	const MODEL = 'Group';
	const LIST_MODEL = 'GroupList';

	const PAGINATABLE = true;


	protected function export_each($group) {
		$export = $group->export();

		foreach($group->persons as $i => $person){
			if(!$person->image->is_empty()){
				$export->persons[$i]->image = new Picture($person->image);
			}
		}

		return $export;
	}
}
?>
