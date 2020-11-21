<?php
namespace Blog\Controller;
use \Blog\Controller\Router;
use \Blog\Config\Controllers;
use Exception;

class ControllerQuery {
	public $router;

	public $class;
	public $alias;
	public $method;
	public $data;

	public $mode;
	public $action;

	public $identifier;
	public $amount;
	public $page;

	public $custom;


	function __construct(Router &$router, string $class, array $settings = []) {
		$this->router = &$router;
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->data = $_POST;

		if(in_array($class, Controllers::REGISTERED)){
			$this->class = $class;
		} else if(in_array(Controllers::ALIASES[$class], Controllers::REGISTERED)){
			$this->class = Controllers::ALIASES[$class];
		} else {
			throw new Exception(); // exception
		}

		if(!empty($settings['alias']) && is_string($settings['alias'])){
			if(!in_array($settings['alias'], ['server', 'site', 'astronauth', 'exception'])){
				$this->alias = $settings['alias'];
			} else {
				throw new Exception(); // exception
			}
		} else {
			$this->name = str_replace('Controller', '', $this->class);
		}

		if(in_array($settings['action'], ['new', 'show', 'edit', 'delete', 'list'])){
			$this->action = $settings['action'];

			if($this->action == 'list'){
				$this->mode = 'multi';
			} else {
				$this->mode = 'single';
			}
		} else {
			throw new Exception(); // exception
		}

		if($this->action == 'list' || $this->action == 'show'){
			if(empty($settings['amount'])){
				$this->amount = 5;
			} else if(is_int($settings['amount']) && $settings['amount'] > 0){
				$this->amount = $settings['amount'];
			} else {
				throw new Exception(); // exception;
			}

			if(empty($settings['page'])){
				$this->page = 1;
			} else if(is_int($settings['page']) && $settings['page'] > 0){
				$this->page = $settings['page'];
			} else if(is_string($settings['page'])){
				$page = $this->router->resolve_substitutions($settings['page']);
				if($page == null){
					$this->page = 1;
				} else if(is_numeric($page) && $page > 0){
					$this->page = (int) $page;
				} else {
					throw new Exception(); // exception
				}
			} else {
				throw new Exception(); // exception
			}
		}

		if($this->action == 'show' || $this->action == 'edit' || $this->action == 'delete'){
			if(is_string($settings['identifier'])){
				$this->identifier = $this->router->resolve_substitutions($settings['identifier']);
			} else {
				throw new Exception(); // exception
			}
		}

		$this->custom = $settings['custom'] ?? [];
	}
}
?>
