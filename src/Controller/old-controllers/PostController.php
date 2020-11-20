<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;
use \Blog\Controller\Processors\Timestamp;
use \Blog\Controller\Processors\MarkdownContent;
use \Blog\Controller\Processors\Pagination\Pagination;
use InvalidArgumentException;


class PostController extends Controller {
	const MODEL = 'Post';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process_each(&$object, &$obj) {
		$obj->timestamp = new Timestamp($object->timestamp);
		$obj->content = new MarkdownContent($object->content);

		if(!$object->image->is_empty()){
			$obj->image = new Picture($object->image);
		}
	}
}
?>
