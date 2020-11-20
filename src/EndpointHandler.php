<?php
namespace Blog;
use \Blog\Config\Config;
use \Blog\Config\Site;
use \Blog\Controller\Router;
use \Astronauth\Backend\User;

class EndpointHandler {
	public $user;
	public $router;
	public $controllers = [];
	public $exception;


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL & ~\E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		set_exception_handler(function($e){
			$this->exception = $e;
			$this->handle(500);
		});

		if(substr($_SERVER['REQUEST_URI'].'/', 0, 7) == '/admin/'){
			$routes_json = file_get_contents(__DIR__ . '/Config/Routes/adminroutes.json');
		} else {
			$routes_json = file_get_contents(__DIR__ . '/Config/Routes/routes.json');
		}

		$this->router = new Router($routes_json);

		$this->user = new User();
		$this->user->authenticate();

		if($this->router->auth == true && !$this->user->is_authenticated()){
			http_response_code(403);
			header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
			exit;
		}

		foreach($this->router->controller_requests as $request){
			$class = '\Blog\Controller\Controllers\\' . $request->class;
			$this->controllers[$request->class] = new $class($request);
		}

		foreach($this->controllers as &$controller){
			$controller->execute();

			if($controller->status == 44){
				$this->handle(404);
				exit;
			} else if($controller->status == 50){
				$this->handle(500);
				exit;
			} else if(!$controller->empty()){
				$controller->process();
			}
		}

		$this->handle();
	}

	// TODO close PDO and disable any file and database access for following procedures

	private function handle(int $response_code = 200) {
		if($response_code == 200){
			$template = __DIR__ . '/View/Templates/' . $this->router->template . '.php';
		} else if($response_code == 404){
			$template = __DIR__ . '/View/Templates/404.php';
		} else if($response_code == 500){
			$template = __DIR__ . '/View/Templates/500.php';
		} else {
			$this->handle(500);
		}

		http_response_code($response_code);

		foreach($this->controllers as $name => $controller){
			$controller_name = $controller->request->name . 'Controller';
			$result_name = $controller->request->name;

			global $$controller_name;
			global $$result_name;
			$$controller_name = $controller;
			$$result_name = $controller->export();
		}


		global $server;
		global $site;
		global $astronauth;
		global $exception;

		$server 	= (object)[
			'version' 		=> Config::VERSION,
			'url' 			=> Config::SERVER_URL,
			'lang' 			=> Config::SERVER_LANG,
			'dyn_img_path' 	=> Config::DYNAMIC_IMAGE_PATH,
			'path' 			=> $this->router->path
		];

		$site 		= (object)[
			'title' 	=> Site::TITLE,
			'twitter' 	=> Site::TWITTER_SITE
		];

		$astronauth = $this->user;
		$exception = $this->exception;

		if(file_exists($template)){
			include $template;
		} else if($response_code == 500){
			echo '<h1>500 Internal Server Error</h1><p>Error: error template not found.</p>';
			throw $this->exception;
		} else {
			$this->handle(500);
		}
	}
}
?>
