<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Config\Config;
use \Blog\Config\Routes;

class Controller {
	public $route;
	public $title;
	public $content;


	function __construct($route) {
		$this->route = $route;
		echo 'Test';
		var_dump(Routes::TITLES);
	}

	public function load() {
		if(!file_exists('frontend/web/pages/' . $_GET['page'] . '.html')){
			return false;
		}

		$this->title = Routes::TITLES[$_GET['page']] ?? 'Neue Seite';
		$this->content = file_get_contents('frontend/web/pages/' . $_GET['page'] . '.html');

		return true;
	}

	public function display() {
		$title = $this->title;
		$content = $this->content;

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
