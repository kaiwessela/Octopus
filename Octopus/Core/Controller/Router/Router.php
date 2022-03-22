<?php
namespace Octopus\Core\Controller\Router;
use \Octopus\Core\Controller\Router\TargetDefinition;
use \Octopus\Core\Controller\Router\ControllerCall;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Response;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Exception;

class Router {
	private array $routes;


	public function load_routes(string|array $path_or_routes) : void {
		if(is_string($path_or_routes)){
			$this->routes = ConfigLoader::read($path_or_routes);
		} else {
			$this->routes = $path_or_routes;
		}
	}


	public function route(Request $request, Response &$response) : array {
		if(!isset($this->routes)){
			throw new ControllerException(500, 'No Routes specified.');
		}

		$found = false;
		$route = null;
		foreach($this->routes as $target => $options){
			$tardef = new TargetDefinition($target);

			if($tardef->match_path($request)){
				$found = true;

				if($tardef->match_method($request)){
					$route = $options;
					break;
				}
			}
		}

		if(!$found){
			throw new ControllerException(404, 'Route not found.');
		} else if(is_null($route)){
			throw new ControllerException(405, 'Route found but Method not allowed.');
		}

		if(isset($route['templates'])){
			$response->set_templates($route['templates']);
		}

		if(isset($route['template'])){
			$response->set_template(0, $route['template']);
		}

		if(isset($route['controllers']) && !is_array($route['controllers'])){
			throw new Exception('controllers is not an array.');
		}

		if(isset($route['entities']) && !is_array($route['entities'])){
			throw new Exception('entities is not an array.');
		}

		$controller_calls = [];

		foreach($route['controllers'] ?? [] as $name => $preferences){
			$call = new ControllerCall($request);
			$call->load_controller($name, $preferences);
			$controller_calls[] = $call;
		}

		foreach($route['entities'] ?? [] as $name => $preferences){
			$call = new ControllerCall($request);
			$call->load_entity($name, $preferences);
			$controller_calls[] = $call;
		}

		return $controller_calls;
	}
}
?>
