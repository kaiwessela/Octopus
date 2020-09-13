<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Routes;
use PDO;
use Exception;

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

		$path = implode('/', [$_GET['1'] ?? '', $_GET['2'] ?? '']);

		foreach(Routes::ROUTES as $route){
			if($route['path'] == '@else'){
				continue;
			}

			if(!preg_match($route['path'], $path)){
				continue;
			}

			$this->route = $route;
		}

		if(!$this->route){
			$this->route = Routes::ROUTES['@else'];
		}

		foreach($this->route['controllers'] as $class => $settings){
			$controller_name = "\Blog\Frontend\Web\Controllers\\${class}Controller";
			$this->controllers[$class] = new $controller_name();
			$this->controllers[$class]->prepare($settings);

			try {
				$this->controllers[$class]->execute();
			} catch(Exception $e){
				$this->return_404();
			}

			$this->controllers[$class]->process();
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
