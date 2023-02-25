<?php
namespace Octopus\Core\Controller;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Response;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Core\Controller\Router\Router;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Config;
use \Exception;

class Endpoint {
	private array $controllers;
	private ?string $primary_controller;
	private Request $request;
	private Response $response;
	private Router $router;
	private ?Exception $exception;
	private DatabaseAccess $db;


	function __construct(array $options = []) {
		ini_set('display_errors', '1');
		error_reporting(E_ALL & ~E_NOTICE);

		$this->controllers = [];
		$this->primary_controller = null;
		$this->request = new Request();
		$this->response = new Response();
		$this->router = new Router();

		if(isset($options['config'])){
			Config::load($options['config']);
		} else {
			Config::load('{OCTOPUS_DIR}/Config/Config.php');
		}

		if(Config::get('Server.debug_mode')){
			ini_set('display_errors', '1');
			error_reporting(E_ALL & ~E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$this->db = new DatabaseAccess(); // TODO

		if(isset($options['modules'])){
			$this->router->load_module_config($options['modules']);
		} else {
			$this->router->load_module_config('{OCTOPUS_DIR}/Config/Modules.php');
		}

		if(isset($options['routes'])){
			$this->router->load_routes($options['routes']);
		} else {
			$this->router->load_routes('{ENDPOINT_DIR}/routes.php');
		}

		if(isset($options['template_dir'])){
			$this->response->set_template_dir($options['template_dir']);
		} else {
			$this->response->set_template_dir('{ENDPOINT_DIR}/templates/');
		}

		setlocale(LC_ALL, Config::get('Server.lang').'.utf-8'); // TEMP
	}


	public function execute() : void { // TODO handle databaseexceptions
		try {
			$controller_calls = $this->router->route($this->request, $this->response);
		} catch(ControllerException $e){
			$this->abort($e);
			return;
		} catch(Exception $e){
			$this->abort(new ControllerException(500, 'Unknown Error.', $e));
			return;
		}

		foreach($controller_calls as $call){
			$controller = $call->create_controller();
			$controller->load_environment($this, $this->request, $call);

			if(isset($this->controllers[$call->get_name()])){
				$this->abort(new ControllerException(500, "Controller name «{$call->get_name()}» already in use."));
				return;
			}

			try {
				$controller->load();
			} catch(ControllerException $e){
				$this->abort($e);
				return;
			} catch(Exception $e){
				throw $e;
				$this->abort(new ControllerException(500, 'Unknown Error.', $e));
				return;
			}

			$this->controllers[$call->get_name()] = $controller;
		}

		$silent_exceptions = [];

		foreach($this->controllers as &$controller){
			try {
				$controller->execute();
			} catch(ControllerException $e){
				if($controller->get_importance() === 'accessory'){
					$controller = null;
					$silent_exceptions[] = $e;
				} else {
					$this->abort($e);
					return;
				}
			} catch(Exception $e){
				throw $e;
				// $this->abort(new ControllerException(500, 'Unknown Error.', $e));
				$this->abort($e);
				return;
			}
		}

		$status_code = 200;
		$environment = [];

		foreach($this->controllers as $name => &$controller){
			try { # actually, controllers should not throw exceptions during finish().
				$controller->finish();
			} catch(ControllerException $e){
				$this->abort($e);
				return;
			} catch(Exception $e){
				$this->abort(new ControllerException(500, 'Unknown Error.', $e));
				return;
			}

			if($controller->get_importance() === 'primary'){
				$status_code = $controller->get_status_code();

				// TODO redirect
			}

			// TODO check this to prevent overwriting
			if($controller instanceof EntityController){
				$environment["{$name}Controller"] = &$controller;
				$environment[$name] = &$controller->object;
			} else {
				$environment[$name] = &$controller;
			}

			$this->response->set_cookies($controller->get_cookies());
		}

		$this->send($status_code, $environment);
	}


	private function abort(ControllerException $exception) : void {
		$environment = [];

		if(Config::get('Server.debug_mode') === true){
			$environment['exception'] = $exception;
		}

		$this->send($exception->get_status_code(), $environment);
	}


	private function send(?int $status_code, array $environment = []) : void {
		$this->db->disable();

		$environment['server'] = (object)[
			'url' => $this->request->get_base_url(), // maybe TEMP
			'lang' => Config::get('Server.lang'),
			'debug_mode' => Config::get('Server.debug_mode'),
			'request' => $this->request,
			'response' => $this->response,
			'base' => $this->request->get_base_path()
		];

		$this->response->send($status_code, $environment);
	}


	public function &get_db() : DatabaseAccess {
		return $this->db;
	}


	public function &get_response() : Response {
		return $this->response;
	}


	public function &get_controller(string $name, bool $silent = false) : ?Controller {
		if(isset($this->controllers[$name])){
			return $this->controllers[$name];
		} else if($silent){
			return null;
		} else {
			throw new ControllerException(500, "Controller «{$name}» not found.");
		}
	}


	// public function &get_authentication_controller() : ?AuthenticationController {
	// 	return null;
	// }



}
?>
