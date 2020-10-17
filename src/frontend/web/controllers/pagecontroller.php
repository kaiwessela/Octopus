<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\MarkdownContent;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


class PageController extends Controller {
	const MODEL = 'Page';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process_each(&$object, &$obj) {
		$obj->content = new MarkdownContent($object->content);
	}
}
?>
