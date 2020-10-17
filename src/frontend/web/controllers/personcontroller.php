<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


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
