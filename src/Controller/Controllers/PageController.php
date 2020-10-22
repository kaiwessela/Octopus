<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\MarkdownContent;
use \Blog\Controller\Processors\Pagination\Pagination;


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
