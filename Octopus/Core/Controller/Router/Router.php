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
	private array $module_config;


	public function load_routes(string|array $path_or_routes) : void {
		if(is_string($path_or_routes)){
			$this->routes = ConfigLoader::read($path_or_routes);
		} else {
			$this->routes = $path_or_routes;
		}
	}


	public function load_module_config(string|array $path_or_modules) : void {
		if(is_string($path_or_modules)){
			$this->module_config = ConfigLoader::read($path_or_modules);
		} else {
			$this->module_config = $path_or_modules;
		}
	}


	public function route(Request $request, Response &$response) : array {
		if(!isset($this->routes)){
			throw new ControllerException(500, 'No Routes specified.');
		}

		$found = false;
		$route = null;
		$general_route = null;
		foreach($this->routes as $target => $options){
			if($target === '@all'){
				$general_route = $options;
				continue;
			}

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

		if(isset($route['allowed_content_types'])){
			if(!is_array($route['allowed_content_types'])){
				throw new Exception('allowed_content_types is not an array.');
			}

			$request->check_content_type($route['allowed_content_types']);
		}

		if(isset($route['templates'])){
			$response->set_templates($route['templates']);
		}

		if(isset($route['template'])){
			$response->set_template(0, $route['template']);
		}

		if(isset($general_route['controllers']) && !is_array($general_route['controllers'])){
			throw new Exception('@all:controllers is not an array.');
		}

		if(isset($general_route['entities']) && !is_array($general_route['entities'])){
			throw new Exception('@all:entities is not an array.');
		}

		if(isset($route['controllers']) && !is_array($route['controllers'])){
			throw new Exception('controllers is not an array.');
		}

		if(isset($route['entities']) && !is_array($route['entities'])){
			throw new Exception('entities is not an array.');
		}

		$controllers = array_merge($general_route['controllers'] ?? [], $route['controllers'] ?? []);
		$entities = array_merge($general_route['entities'] ?? [], $route['entities'] ?? []);

		$controller_calls = [];
		$has_entity_controller = false;

		foreach($controllers as $name => $preferences){
			$call = new ControllerCall($request, $this->module_config);
			$call->load_controller($name, $preferences);
			$controller_calls[] = $call;
		}

		foreach($entities as $name => $preferences){
			$has_entity_controller = true;
			$call = new ControllerCall($request, $this->module_config);
			$call->load_entity($name, $preferences);
			$controller_calls[] = $call;
		}

		$primary_found = false;
		$essential_found = false;
		foreach($controller_calls as $call){
			if($call->get_importance() === 'primary'){
				if($primary_found === true){
					throw new ControllerException(500, 'there can only be one primary controller');
				}

				$primary_found = true;
			} else if($call->get_importance() === 'essential'){
				$essential_found = true;
			}
		}

		if($essential_found === true && $primary_found === false){
			foreach($controller_calls as $call){
				if($has_entity_controller === true){
					if($call->is_entity_controller()){
						$call->set_importance('primary');
						break;
					}
				} else {
					$call->set_importance('primary');
					break;
				}
			}
		}

		return $controller_calls;
	}
}
?>
