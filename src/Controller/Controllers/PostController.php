<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Timestamp;
use \Blog\Frontend\Web\Modules\MarkdownContent;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;
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
