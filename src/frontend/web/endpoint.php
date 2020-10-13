<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Controllers;
use \Blog\Frontend\Web\SiteConfig;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Frontend\Web\Modules\Router;
use \Astronauth\Backend\User;
use PDO;
use Exception;

class Endpoint {
	public $user;
	public $router;
	public $controllers;


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL & ~\E_NOTICE);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$this->router = new Router();

		if(!$this->router->route){
			$this->return_404();
		}

		$this->user = new User();
		$this->user->authenticate();

		if($this->router->route['auth'] ?? false == true){
			if(!$this->user->is_authenticated()){
				header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
				exit;
			}
		}

		$this->controllers = [];
		foreach($this->router->route['controllers'] as $name => $settings){
			$name = $this->router->path_resolve($name);

			if(!in_array($name, Controllers::REGISTERED)){
				$name = Controllers::ALIASES[$name];
			}

			if(!in_array($name, Controllers::REGISTERED)){
				$this->return_404();
			}

			$absolute_name = '\Blog\Frontend\Web\Controllers\\' . $name;
			$this->controllers[$name] = new $absolute_name($this);
			$this->controllers[$name]->prepare($settings);

			try {
				$this->controllers[$name]->execute();
			} catch(Exception $e){
				$this->return_404();
			}

			$this->controllers[$name]->process();
		}
	}

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
			'url' => Config::SERVER_URL,
			'lang' => Config::SERVER_LANG,
			'dyn_img_path' => Config::DYNAMIC_IMAGE_PATH,
			'path' => $this->router->path
		];

		global $site;
		$site = (object) [
			'title' => SiteConfig::TITLE,
			'twitter' => SiteConfig::TWITTER_SITE
		];

		global $timeformat;
		$timeformat = new TimeFormat;

		global $astronauth;
		$astronauth = $this->user;

		include __DIR__ . '/templates/' . $this->router->path_resolve($this->router->route['template']) . '.tmp.php';
	}

	function return_404() {
		http_response_code(404);
		include 'templates/404.tmp.php';
		exit;
	}
}
?>
