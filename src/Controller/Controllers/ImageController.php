<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


class ImageController extends Controller {
	const MODEL = 'Image';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process_each(&$object, &$obj) {
		$obj = new Picture($object);
	}
}
?>
