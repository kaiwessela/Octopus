<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Routes;
use \Astronauth\Backend\User;
use PDO;
use Exception;

class Endpoint {
	public $user;
	public $route;
	public $controllers;


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$get = [];
		foreach($_GET as $key => $value){
			if(is_numeric($key) && strlen($value) != 0){
				$_GET[$key] = str_replace(['/', '\\'], ['', ''], $value);
				$get[(int) $key] = $_GET[$key];
			}
		}

		$path = implode('/', $get);

		foreach(Routes::ROUTES as $route){
			if($route['path'] == '@else'){
				$else_route = $route;
				continue;
			}

			if(preg_match($route['path'], $path)){
				$this->route = $route;
				break;
			}
		}

		if(!$this->route){
			$this->route = $else_route;
		}

		$this->user = new User();
		$this->user->authenticate();

		if($this->route['auth'] ?? false == true){
			if(!$this->user->is_authenticated()){
				header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
				exit;
			}
		}

		if(!empty($this->route['controllers'])){
			foreach($this->route['controllers'] as $class => $settings){
				if(preg_match('/^\?([0-9])$/', $class, $matches)){
					$classaliases = [
						'event' => 'Event',
						'events' => 'Event',
						'image' => 'Image',
						'images' => 'Image',
						'page' => 'Page',
						'pages' => 'Page',
						'person' => 'Person',
						'persons' => 'Person',
						'post' => 'Post',
						'posts' => 'Post'
					];

					$class = $classaliases[(empty($_GET[$matches[1]])) ? null : $_GET[$matches[1]]];
				}

				$controller_name = '\Blog\Frontend\Web\Controllers\\' . $class . 'Controller';
				$this->controllers[$class] = new $controller_name();
				$this->controllers[$class]->prepare($settings);

				try {
					$this->controllers[$class]->execute();
				} catch(Exception $e){
					$this->return_404();
				}

				$this->controllers[$class]->process();
			}
		}
	}

	public function handle() {
		if(!empty($this->controllers)){
			foreach($this->controllers as $name => $controller){
				global $$name;
				$$name = $controller;
			}
		}

		global $server;
		$server = (object) [
			'url' => Config::SERVER_URL,
			'lang' => Config::SERVER_LANG,
			'dyn_img_path' => Config::DYNAMIC_IMAGE_PATH
		];

		global $astronauth;
		$astronauth = $this->user;

		$template = $this->route['template'];

		$template = str_replace(
			['?1', '?2', '?3', '?4', '?5', '?6', '?7', '?8', '?9'],
			[$_GET['1'], $_GET['2'], $_GET['3'], $_GET['4'], $_GET['5'], $_GET['6'], $_GET['7'], $_GET['8'], $_GET['9']],
			$template
		);

		$template = __DIR__ . '/templates/' . $template . '.tmp.php';

		include $template;
	}

	function return_404() {
		http_response_code(404);
		include 'templates/404.tmp.php';
		exit;
	}
}
?>
