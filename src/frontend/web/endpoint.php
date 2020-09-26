<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Routes;
use \Blog\Config\Controllers;
use \Blog\Frontend\Web\SiteConfig;
use \Blog\Frontend\Web\Modules\TimeFormat;
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
			error_reporting(\E_ALL & ~\E_NOTICE);
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
			if(preg_match($route['path'], $path)){
				$this->route = $route;
				break;
			}
		}

		if(!$this->route){
			$this->return_404();
		}

		$this->user = new User();
		$this->user->authenticate();

		if($this->route['auth'] ?? false == true){
			if(!$this->user->is_authenticated()){
				header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
				exit;
			}
		}

		$this->controllers = [];
		foreach($this->route['controllers'] as $name => $settings){
			if(preg_match('/^\?([0-9])$/', $name, $matches)){
				$name = Controllers::ALIASES[$_GET[$matches[1]]];
			}

			if(!in_array($name, Controllers::REGISTERED)){
				$this->return_404();
			}

			$absolute_name = '\Blog\Frontend\Web\Controllers\\' . $name;
			$this->controllers[$name] = new $absolute_name();
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
			'dyn_img_path' => Config::DYNAMIC_IMAGE_PATH
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
