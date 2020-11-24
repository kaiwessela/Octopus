<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;

class PersonController extends Controller {
	const MODEL = 'Person';
	const LIST_MODEL = 'PersonList';


	protected function export_each($person) {
		$export = $person->export();

		if(!$person->image->is_empty()){
			$export->image = new Picture($person->image);
		}

		return $export;
	}
}
?>
