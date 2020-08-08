<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Config\Config;

class Controller {
	public $route;
	public $content;


	function __construct($route) {
		$this->route = $route;
	}

	public function load() {
		if(!file_exists('frontend/web/pages/' . $_GET['page'] . '.html')){
			return false;
		}

		$this->content = file_get_contents('frontend/web/pages/' . $_GET['page'] . '.html');

		return true;
	}

	public function display() {
		$server = (object) [
			'url' => Config::SERVER_URL
		];
		$content = $this->content;

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
