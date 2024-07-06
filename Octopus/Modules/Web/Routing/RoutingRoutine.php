<?php
namespace Octopus\Modules\Web\Routing;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Modules\Web\Routing\Route;
use Octopus\Modules\Web\Routing\RoutingException;
use Octopus\Modules\Web\WebRoutine;

class RoutingRoutine extends WebRoutine {
	protected array $routes;
	protected Route $default_route;
	protected Route $active_route;


	function __construct(array $routes) {
		foreach($routes as $target => $options){
			$route = new Route($target, $options, $this);

			if($target === '@default'){
				$this->default_route = $route;
			} else {
				$this->routes[] = $route;
			}
		}
	}


	public function run(Environment &$env) : void {
		$route_found = false;
		$method_matches = false;
		foreach($this->routes as &$route){
			if($route->match_path($env->get_request()->get_virtual_path())){
				$route_found = true;

				if($route->match_method($env->get_request()->get_method())){
					$method_matches = true;
					$this->active_route = $route;
					break;
				}
			}
		}

		if(!$route_found){
			throw new RoutingException(404, 'Not Found.');
		}

		if(!$method_matches){
			throw new RoutingException(405, 'Method Not Allowed.');
		}

		foreach($this->active_route->get_options()['routines'] as $name => $settings){
			$settings = $settings + ($this->default_route->get_options()["{$name}.routine"] ?? []) + ($this->default_route->get_options()['*.routine'] ?? []);

			$routine_class = $settings['routine'] 
				?? $this->default_route->get_options()['routines']['*']['routine']
				?? null;

			if(!class_exists($routine_class)){
				throw new RoutingException(500, "Routine class «{$routine_class}» not found.");
			}

			if(!is_subclass_of($routine_class, WebRoutine::class)){
				throw new RoutingException(500, "Routine class «{$routine_class}» is not a WebRoutine.");
			}

			$routine = $routine_class::create_from_route($settings, $env);

			try {
				$env->run($routine, $name, pass_errors:true);
			} catch(Exception $e){
				throw $e;
			}
		}
	}


	public function get_route() : Route {
		return $this->active_route;
	}
}