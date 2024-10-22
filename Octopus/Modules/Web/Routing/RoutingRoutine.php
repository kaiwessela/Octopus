<?php
namespace Octopus\Modules\Web\Routing;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Exceptions\ControllerException;
use Octopus\Core\Controller\Routine;
use Octopus\Core\Controller\StandardRoutine;
use Octopus\Modules\Web\Routing\Route;
use Octopus\Modules\Web\Routing\RoutingException;
use Octopus\Modules\Web\WebRoutine;

class RoutingRoutine extends StandardRoutine implements Routine {
	protected array $routes;
	protected Route $default_route;
	protected Route $active_route;
	protected string $template;
	public Exception $exception; // TEMP


	public function load(array $routes) {
		$this->routes = [];

		$this->active_route = new Route('@empty', [], $this);

		foreach($routes as $target => $options){
			$route = new Route($target, $options, $this);

			if($target === '@default'){
				$this->default_route = $route;
			} else {
				$this->routes[] = $route;
			}
		}

		if(!isset($this->default_route)){
			$this->default_route = new Route('@default', [], $this);
		}
	}


	public function run() : void {
		$route_found = false;
		$method_matches = false;
		foreach($this->routes as &$route){
			if($route->match_path($this->environment->get_request()->get_virtual_path())){
				$route_found = true;

				if($route->match_method($this->environment->get_request()->get_method())){
					$method_matches = true;
					$this->active_route = $route;
					break;
				}
			}
		}

		if(!$route_found){
			$this->handle_exception(new RoutingException(404, 'Not Found.'));
			return;
		}

		if(!$method_matches){
			$this->handle_exception(new RoutingException(405, 'Method Not Allowed.'));
			return;
		}

		$this->template = $this->active_route->get_options()['template'];

		foreach($this->active_route->get_options()['routines'] ?? [] as $name => $settings){
			$settings = $settings + ($this->default_route->get_options()["{$name}.routine"] ?? []) + ($this->default_route->get_options()['*.routine'] ?? []);

			$routine_class = $settings['routine'] 
				?? $this->default_route->get_options()['routines']['*']['routine']
				?? null;

			if(!class_exists($routine_class)){
				$this->handle_exception(new RoutingException(500, "Routine class «{$routine_class}» not found."));
				return;
			}

			if(!is_subclass_of($routine_class, WebRoutine::class)){
				$this->handle_exception(new RoutingException(500, "Routine class «{$routine_class}» is not a WebRoutine."));
				return;
			}

			$routine = new $routine_class();
			$routine->load($settings);

			try {
				$this->environment->run($routine, $name, pass_errors:true);
			} catch(Exception $e){
				$this->handle_exception($e);
			}
		}
	}


	protected function handle_exception(Exception $e) : void {
		if($e instanceof ControllerException){
			$settings = ($this->active_route->get_options()['exceptions'] ?? []) + ($this->default_route->get_options()['exceptions'] ?? []);

			$setting = $settings[$e->get_status_code()] ?? $settings[(int) floor($e->get_status_code() / 100)] ?? $settings['*'] ?? null;

			if(is_array($setting)){
				$template = $setting['template'];
			} else if(is_string($setting)){
				$template = $setting;
			}

			if(isset($template)){
				$this->environment->get_response()->set_status_code($e->get_status_code());
				$this->template = $template;
				$this->exception = $e;
				return;
			}
		}

		throw $e;
	}


	public function get_route() : Route {
		return $this->active_route;
	}


	public function get_template() : string {
		return $this->template;
	}
}