<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;
use \Blog\Controller\Processors\Pagination\Pagination;


class PersonController extends Controller {
	const MODEL = 'Person';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process_each(&$object, &$obj) {
		if(!$object->image->is_empty()){
			$obj->image = new Picture($object->image);
		}
	}
}
?>
