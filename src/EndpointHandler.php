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


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL & ~\E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		if(substr($_SERVER['REQUEST_URI'].'/', 0, 7) == '/admin/'){
			$routes_json = file_get_contents(__DIR__ . '/Config/Routes/adminroutes.json');
		} else {
			$routes_json = file_get_contents(__DIR__ . '/Config/Routes/routes.json');
		}

		$this->router = new Router($routes_json);

		$this->user = new User();
		$this->user->authenticate();

		if($this->router->auth == true){
			if(!$this->user->is_authenticated()){
				header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
				exit;
			}
		}

		foreach($this->router->controller_requests as $request){
			$class = '\Blog\Controller\Controllers\\' . $request->class;
			$this->controllers[$request->class] = new $class($request);
		}

		foreach($this->controllers as &$controller){
			$controller->execute();

			if($controller->status == 44){
				// TEMP
				foreach($controller->exceptions as $e){
					throw $e;
				}
			} else if($controller->status == 50){
				// TEMP
				foreach($controller->exceptions as $e){
					throw $e;
				}
			} else if(!$controller->empty()){
				$controller->process();
			}
		}
	}

	// TODO close PDO and disable any file and database access for following procedures

	public function handle() {
		foreach($this->controllers as $name => $controller){
			global $$name;
			$$name = $controller;

			$shortname = str_replace('Controller', '', $name);
			global $$shortname;
			$$shortname = &$$name;
		}

		global $server;
		$server = (object) [
			'version' => Config::VERSION,
			'url' => Config::SERVER_URL,
			'lang' => Config::SERVER_LANG,
			'dyn_img_path' => Config::DYNAMIC_IMAGE_PATH,
			'path' => $this->router->path
		];

		global $site;
		$site = (object) [
			'title' => Site::TITLE,
			'twitter' => Site::TWITTER_SITE
		];

		global $astronauth;
		$astronauth = $this->user;

		include __DIR__ . '/View/Templates/' . $this->router->template . '.php';
	}

	function return_404() {
		http_response_code(404);
		include 'View/Templates/404.tmp.php';
		exit;
	}
}
?>
