<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Page;
use \Blog\Model\DataObjects\Lists\PageList;

class PageController extends Controller {
	const MODEL = Page::class;
	const LIST_MODEL = PageList::class;
}
?>
