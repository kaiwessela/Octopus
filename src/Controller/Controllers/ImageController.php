<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;
use \Blog\Controller\Processors\Pagination\Pagination;


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
