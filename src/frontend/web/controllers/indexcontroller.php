<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;

class IndexController extends Controller {
	public function load() {
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
