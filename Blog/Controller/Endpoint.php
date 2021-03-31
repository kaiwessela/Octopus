<?php
namespace Blog\Controller;
use \Astronauth\Main as Astronauth;
use \Blog\Config\Config;
use \Blog\Config\Site;
use \Blog\Controller\Request;
use \Blog\Controller\Response;
use \Blog\Controller\PathNotation;
use \Blog\Controller\Call;
use Exception;

class Endpoint {
	private bool $aborted;
	public Request $request;
	public Response $response;
	public Astronauth $astronauth;
	public ?Exception $exception;
	public string $template_path;
	public string $template;
	public ?array $calls;
	public ?array $controllers;
	public bool $require_auth;


	function __construct(string $template_path) {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL & ~\E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		if(is_dir($template_path)){
			$this->template_path = rtrim($template_path, '/') . '/';
		} else {
			exit('invalid template path.');
		}

		$this->request = new Request();
		$this->response = new Response();

		$this->astronauth = new Astronauth();
		$this->astronauth->authenticate();

		$this->aborted = false;
		$this->exception = null;

		$this->require_auth = false;

		$this->calls = [];
		$this->controllers = [];
	}


	public function route(array $routes) {
		if($this->aborted){
			return;
		}

		try {

			if(!is_array($routes) || empty($routes)){
				throw new Exception('Router » routes is not an array or empty.');
			}

			foreach($routes as $pn => $rt){
				$pathnotation = new PathNotation($pn);

				if($pathnotation->match(trim($this->request->path, '/'))){
					$route = $rt;
					break;
				}
			}

			if(!isset($route)){
				// no route found - 404
				$this->abort(404);
				return;
			}

			$this->template = $route['template']; // TODO validity check and more and new everything
			if(empty($this->template)){
				throw new Exception("Router » invalid template: '$this->template'.");
			}


			if(isset($route['methods'])){
				if(!is_array($route['methods'])){
					throw new Exception('Router » invalid methods.');
				}

				$this->request->merge_allowed_methods($route['methods']);
			}

			if(isset($route['contentTypes'])){
				if(!is_array($route['contentTypes'])){
					throw new Exception('Router » invalid contentTypes.');
				}

				$this->request->merge_allowed_content_types($route['contentTypes']);
			}

			if(isset($route['require_auth']) && $route['require_auth'] == true){
				$this->require_auth = true;
			}


			foreach($route['controllers'] ?? [] as $class => $settings){
				$this->calls[] = new Call($class, $settings, 'controller', $this->request);
			}

			foreach($route['objects'] ?? [] as $class => $settings){
				$this->calls[] = new Call($class, $settings, 'object', $this->request);
			}

		} catch(Exception $e){
			$this->abort($e->getCode(), $e);
			return;
		}
	}


	public function prepare() : void {
		if($this->aborted){
			return;
		}

		try {

			if($this->request->check_method() == false){
				// 405 Method Not Allowed
				$this->abort(405);
				return;
			}

			if($this->request->check_content_type() == false){
				// 415 Unsupported Media Type
				$this->abort(415);
				return;
			}

			if($this->require_auth && !$this->astronauth->is_authenticated()){
				// TEMP
				http_response_code(403);
				header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
				exit;

				// 403 Forbidden
				//$this->response->set_code(403);
				//return;
			}

			foreach($this->calls as $call){
				$cls = $call->controller;
				$this->controllers[$call->varname] = new $cls($this->request, $this->astronauth);
				$this->controllers[$call->varname]->prepare($call);
			}

		} catch(Exception $e){
			$this->abort($e->getCode(), $e);
			return;
		}
	}


	public function execute() {
		if($this->aborted){
			return;
		}

		try {

			foreach($this->controllers as &$controller){
				$controller->execute();

				// TODO status code handling
			}

		} catch(Exception $e){
			$this->abort($e->getCode(), $e);
			return;
		}
	}


	public function send() {
		// TEMP TEMP TEMP
		global $server;
		global $site;
		global $astronauth;
		global $exception;

		$server = (object)[
			'version' => Config::VERSION,
			'url' => Config::SERVER_URL,
			'lang' => Config::SERVER_LANG,
			'dyn_img_path' => Config::DYNAMIC_IMAGE_PATH, // DEPRECATED,
			'path' => trim($this->request->path, '/')
		];

		$site = (object)[
			'title' => Site::TITLE,
			'twitter' => Site::TWITTER_SITE
		];

		$astronauth = $this->astronauth;

		$exception = (Config::DEBUG_MODE) ? $this->exception : null;

		foreach($this->controllers as $varname => $controller){
			$conname = $varname . 'Controller';

			global $$conname;
			global $$varname;

			if(isset($$conname) || isset($$varname)){
				continue;
				// Exception
			}

			$$conname = $controller;
			$controller->process();
			$$varname = $controller->object;
		}

		$this->response->send();

		require $this->template_path . $this->template . '.php';
	}


	private function abort(mixed $code, ?Exception $e = null) : void {
		$this->aborted = true;

		try {
			$this->response->set_code($code);
		} catch(Exception $f){
			$this->response->set_code(500);
		}

		$this->exception = $e;
	}



}
?>
