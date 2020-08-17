<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Routes;

class StaticController implements Controller {
	public $title;
	public $content;

	function __construct($route, $settings) {
		if(file_exists(__DIR__ . '/../pages/' . $_GET['page'] . '.html')){
			$this->content = file_get_contents(__DIR__ . '/../pages/' . $_GET['page'] . '.html');
		} else {
			throw new Exception('not found');
		}

		$this->title = Routes::TITLES[$_GET['page']] ?? '';
	}
}
?>
