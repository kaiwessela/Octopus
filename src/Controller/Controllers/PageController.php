<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\MarkdownContent;

class PageController extends Controller {
	const MODEL = 'Page';
	const LIST_MODEL = 'PageList';


	protected function export_each($page) {
		$export = $page->export();
		// $export->content = new MarkdownContent($page->content);

		return $export;
	}
}
?>
