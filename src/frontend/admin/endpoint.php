<?php
namespace Blog\Frontend\Admin;
use \Astronauth\Backend\User;
use \Blog\Config\Config;
use \Blog\Frontend\Admin\Controllers\StartController;
use \Blog\Frontend\Admin\Controllers\ImageListController;
use \Blog\Frontend\Admin\Controllers\ImageViewController;
use \Blog\Frontend\Admin\Controllers\ImageEditController;
use \Blog\Frontend\Admin\Controllers\ImageNewController;
use \Blog\Frontend\Admin\Controllers\ImageDeleteController;
use \Blog\Frontend\Admin\Controllers\PostListController;
use \Blog\Frontend\Admin\Controllers\PostViewController;
use \Blog\Frontend\Admin\Controllers\PostEditController;
use \Blog\Frontend\Admin\Controllers\PostNewController;
use \Blog\Frontend\Admin\Controllers\PostDeleteController;
use \Blog\Frontend\Admin\Controllers\NotFoundController;

class Endpoint {
	public $controller;
	public $user;

	function __construct() {
		$this->user = new User();
		$this->user->authenticate();
		if(!$this->user->is_authenticated()){
			header('Location: ' . Config::SERVER_URL . '/astronauth/signin');
			exit;
		}

		$request = [];

		if($_GET['class'] ?? false){
			$request[] = $_GET['class'];
		}

		if($_GET['identifier'] ?? false){
			$request[] = $_GET['identifier'];
		}

		if($_GET['action'] ?? false){
			$request[] = $_GET['action'];
		}

		$path = implode('/', $request);

		if(preg_match('/^$/', $path)){
			$this->controller = new StartController();
		} else if(preg_match('/^images$/', $path)){
			$this->controller = new ImageListController();
		} else if(preg_match('/^images\/new$/', $path)){
			$this->controller = new ImageNewController();
		} else if(preg_match('/^images\/.{8}$/', $path)){
			$this->controller = new ImageViewController();
		} else if(preg_match('/^images\/.{8}\/edit$/', $path)){
			$this->controller = new ImageEditController();
		} else if(preg_match('/^images\/.{8}\/delete$/', $path)){
			$this->controller = new ImageDeleteController();
		} else if(preg_match('/^posts$/', $path)){
			$this->controller = new PostListController();
		} else if(preg_match('/^posts\/new$/', $path)){
			$this->controller = new PostNewController();
		} else if(preg_match('/^posts\/.{8}$/', $path)){
			$this->controller = new PostViewController();
		} else if(preg_match('/^posts\/.{8}\/edit$/', $path)){
			$this->controller = new PostEditController();
		} else if(preg_match('/^posts\/.{8}\/delete$/', $path)){
			$this->controller = new PostDeleteController();
		} else {
			$this->controller = new NotFoundController();
		}
	}

	public function handle() {
		$controller = $this->controller;
		$user = $this->user;

		include 'templates/main.php';
	}
}
