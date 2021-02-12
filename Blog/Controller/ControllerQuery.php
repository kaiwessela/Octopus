<?php
namespace Blog\Controller;
use \Blog\Controller\Router;
use \Blog\Controller\Exceptions\InvalidRouteAttributeException;
use \Blog\Config\Controllers;
use Exception;

class ControllerQuery {
	public $router;

	public $class;
	public $name;
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

		if(!empty($settings['alias'])){
			$alias = $class;

			if(in_array($alias, ['Controller', 'Object', 'server', 'site', 'astronauth'])){
				throw new InvalidRouteAttributeException('alias', $alias, 'contains invalid words');
			}

			$class = $settings['alias'];
		}

		if(in_array($class, Controllers::REGISTERED)){
			$this->class = $class;
		} else if(in_array(Controllers::ALIASES[$class], Controllers::REGISTERED)){
			$this->class = Controllers::ALIASES[$class];
		} else {
			throw new InvalidRouteAttributeException('ControllerClass', $class, 'controller not found');
		}

		if(!empty($alias)){
			$this->name = $alias;
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
			throw new InvalidRouteAttributeException('action', $settings['action'], 'invalid action');
		}

		if($this->action == 'list' || $this->action == 'show'){
			if(empty($settings['amount'])){
				$this->amount = 10; // TODO set default in config
			} else if(is_int($settings['amount']) && $settings['amount'] > 0){
				$this->amount = $settings['amount'];
			} else {
				throw new InvalidRouteAttributeException('amount', var_export($settings['amount']));
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
					throw new InvalidRouteAttributeException('page', var_export($settings['page']));
				}
			} else {
				throw new InvalidRouteAttributeException('page', var_export($settings['page']));
			}
		}

		if($this->action == 'show' || $this->action == 'edit' || $this->action == 'delete'){
			if(is_string($settings['identifier'])){
				$this->identifier = $this->router->resolve_substitutions($settings['identifier']);
			} else {
				throw new InvalidRouteAttributeException('identifier', var_export($settings['identifier']));
			}
		}

		$this->custom = $settings['custom'] ?? [];
	}
}
?>
