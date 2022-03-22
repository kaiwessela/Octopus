<?php
namespace Octopus\Core\Controller;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Response;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Core\Controller\Router\Router;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Config;
use \Exception;

class Endpoint {
	private array $controllers;
	public Request $request;
	public Response $response;
	public ?Exception $exception;


	function __construct(array $options = []) {
		$this->request = new Request();
		$this->response = new Response();
		$this->router = new Router($this);

		if(isset($options['config'])){
			Config::load($options['config']);
		} else {
			Config::load('{OCTOPUS_DIR}/Config/Config.php');
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

		if(Config::get('Server.debug_mode')){
			ini_set('display_errors', '1');
			error_reporting(E_ALL & ~E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}
	}


	public function execute() {
		$this->controllers = [];

		foreach($this->router->route($this->request, $this->response) as $call){
			$controller = $call->create_controller();

			if(isset($this->controllers[$call->get_name()])){
				throw new ControllerException(500, "Controller name «{$call->get_name()}» already in use.");
			}

			$controller->load($this->request, $call);

			$this->controllers[$call->get_name()] = $controller;
		}

		foreach($this->controllers as &$controller){
			$controller->execute();
		}

		$environment = [
			'server' => (object)[
				'url' => $this->request->get_base_url() // maybe TEMP
			]
		];

		foreach($this->controllers as $name => &$controller){
			$controller->finish();

			$environment["{$name}Controller"] = &$controller;

			if($controller instanceof EntityController){
				if(isset($controller->entity)){
					$environment[$name] = &$controller->entity;
				} else {
					$environment[$name] = &$controller->entities;
				}
			}
		}

		$this->response->send(200, $environment); // TODO send main controller's status code
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
