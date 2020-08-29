<?php
namespace Blog\Frontend\Admin;
use \Astronauth\Backend\User;
use \Blog\Config\Config;
use \Blog\Frontend\Admin\Controllers\StartController;
use \Blog\Frontend\Admin\Controllers\ListController;
use \Blog\Frontend\Admin\Controllers\ViewController;
use \Blog\Frontend\Admin\Controllers\EditController;
use \Blog\Frontend\Admin\Controllers\NewController;
use \Blog\Frontend\Admin\Controllers\DeleteController;
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
			$this->controller = new ListController('image_list', 'Image');
		} else if(preg_match('/^images\/new$/', $path)){
			$this->controller = new NewController('image_new', 'Image');
		} else if(preg_match('/^images\/.{8}$/', $path)){
			$this->controller = new ViewController('image_view', 'Image');
		} else if(preg_match('/^images\/.{8}\/edit$/', $path)){
			$this->controller = new EditController('image_edit', 'Image');
		} else if(preg_match('/^images\/.{8}\/delete$/', $path)){
			$this->controller = new DeleteController('image_delete', 'Image');
		} else if(preg_match('/^posts$/', $path)){
			$this->controller = new ListController('post_list', 'Post');
		} else if(preg_match('/^posts\/new$/', $path)){
			$this->controller = new NewController('post_new', 'Post');
		} else if(preg_match('/^posts\/.{8}$/', $path)){
			$this->controller = new ViewController('post_view', 'Post');
		} else if(preg_match('/^posts\/.{8}\/edit$/', $path)){
			$this->controller = new EditController('post_edit', 'Post');
		} else if(preg_match('/^posts\/.{8}\/delete$/', $path)){
			$this->controller = new DeleteController('post_delete', 'Post');
		} else if(preg_match('/^persons$/', $path)){
			$this->controller = new ListController('person_list', 'Person');
		} else if(preg_match('/^persons\/new$/', $path)){
			$this->controller = new NewController('person_new', 'Person');
		} else if(preg_match('/^persons\/.{8}$/', $path)){
			$this->controller = new ViewController('person_view', 'Person');
		} else if(preg_match('/^persons\/.{8}\/edit$/', $path)){
			$this->controller = new EditController('person_edit', 'Person');
		} else if(preg_match('/^persons\/.{8}\/delete$/', $path)){
			$this->controller = new DeleteController('person_delete', 'Person');
		} else if(preg_match('/^events$/', $path)){
			$this->controller = new ListController('event_list', 'Event');
		} else if(preg_match('/^events\/new$/', $path)){
			$this->controller = new NewController('event_new', 'Event');
		} else if(preg_match('/^events\/.{8}$/', $path)){
			$this->controller = new ViewController('event_view', 'Event');
		} else if(preg_match('/^events\/.{8}\/edit$/', $path)){
			$this->controller = new EditController('event_edit', 'Event');
		} else if(preg_match('/^events\/.{8}\/delete$/', $path)){
			$this->controller = new DeleteController('event_delete', 'Event');
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
