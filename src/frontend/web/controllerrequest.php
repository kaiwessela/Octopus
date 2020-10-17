<?php
namespace Blog\Frontend\Web;
use \Blog\Frontend\Web\Modules\Router;
use \Blog\Config\Controllers;
use Exception;

class ControllerRequest {
	private $router;

	public $class;
	public $method;
	public $data;

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

		if(in_array($settings['action'], ['new', 'show', 'edit', 'delete', 'list'])){
			$this->action = $settings['action'];
		} else {
			throw new Exception(); // exception
		}

		if($this->action == 'list'){
			if(!isset($settings['amount'])){
				$this->amount = 5;
			} else if(is_int($settings['amount']) && $settings['amount'] > 0){
				$this->amount = $settings['amount'];
			} else {
				throw new Exception(); // exception;
			}

			if(!isset($settings['page'])){
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
		} else if($this->action == 'show' || $this->action == 'edit' || $this->action == 'delete'){
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
