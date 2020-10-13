<?php
namespace Blog\Frontend\Web\Modules;
use \Blog\Config\Routes;

class Router {
	public $path;
	public $route;


	function __construct() {
		$this->path = trim($_SERVER['REQUEST_URI'], '/');

		foreach(Routes::ROUTES as $route){
			if(preg_match($route['path'], $this->path)){
				$this->route = $route;
				break;
			}
		}
	}

	public function path_resolve($input) {
		$segments = explode('/', $this->path);

		return preg_replace_callback('/\?([0-9]+)/', function($matches) use ($segments){
			return $segments[$matches[1] - 1];
		}, $input);
	}
}
?>
