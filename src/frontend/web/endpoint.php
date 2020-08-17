<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Routes;
use \Blog\Frontend\Web\Controllers\IndexController;
use \Blog\Frontend\Web\Controllers\PostController;
use \Blog\Frontend\Web\Controllers\PostListController;
use \Blog\Frontend\Web\Controllers\StaticController;
use PDO;

class Endpoint {
	public $route;
	public $controllers;


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$path = implode('/', [$_GET['page'] ?? '', $_GET['post'] ?? '']);

		foreach(Routes::ROUTES as $route){
			if(!preg_match($route['path'], $path)){
				continue;
			}

			$this->route = $route;
		}

		if(!$this->route){
			$this->route = Routes::STATIC_ROUTE;
		}

		try {
			foreach($this->route['controllers'] as $class => $settings){
				$controller_name = '\Blog\Frontend\Web\Controllers\\' . $class;
				$this->controllers[$class] = new $controller_name($route, $settings);
			}
		} catch(Exception $e){
			$this->return_404();
		}
	}

	public function handle() {
		foreach($this->controllers as $name => $controller){
			$$name = $controller;
		}

		include __DIR__ . '/templates/' . $this->route['template'] . '.tmp.php';
	}

	function return_404() {
		http_response_code(404);
		include 'templates/404.tmp.php';
		exit;
	}
}
?>
