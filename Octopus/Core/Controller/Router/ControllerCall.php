<?php
namespace Octopus\Core\Controller\Router;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Core\Model\Entity;
use \Exception;

// TEMP: This entire class contains temporary code

class ControllerCall {
	private Request $request;
	private array $module_config;
	private string $name;
	private string $importance;
	private string $controller_class;
	private ?string $entity_class;
	private array $options;


	function __construct(Request &$request, array $module_config) {
		$this->request = $request;
		$this->module_config = $module_config;
	}


	public function load_controller(string $name, array $options) : void {
		throw new Exception('Temporarily unimplemented.'); // TEMP
	}


	public function load_entity(string $name, array $options) : void { // TEMP, TODO improve
		$this->name = $name;

		if(isset($options['importance'])){
			if(in_array($options['importance'], ['primary', 'essential', 'accessory'])){
				$this->importance = $options['importance'];
			} else {
				throw new ControllerException(500, 'invalid importance');
			}
		} else {
			$this->importance = 'essential';
		}

		if(empty($options['class'])){
			throw new ControllerException(500, "Entity class missing in route «{$name}».");
		} else if(!is_string($options['class'])){
			throw new ControllerException(500, "Entity class invalid in route «{$name}».");
		}

		$entity_class_name = URLSubstitution::replace($options['class'], $this->request);

		if(!isset($this->module_config['entities'][$entity_class_name])){
			if($options['class'] !== $entity_class_name){ // entity class is dynamic; TODO improve
				throw new ControllerException(404, "Entity class «{$entity_class_name}» not found.");
			} else {
				throw new ControllerException(500, "Entity class «{$entity_class_name}» not found.");
			}
		}

		$entity_class = $this->module_config['entities'][$entity_class_name];

		if(!is_subclass_of($entity_class, Entity::class)){
			throw new ControllerException(500, "Entity class «{$entity_class}» is not an Entity class.");
		}

		$this->entity_class = $entity_class;

		if(isset($options['controller'])){
			$controller_class_name = $options['controller'];
		} else if(isset($this->module_config['default_entity_controllers'][$entity_class])){
			$controller_class_name = $this->module_config['default_entity_controllers'][$entity_class];
		} else if(isset($this->module_config['default_entity_controllers']['@all'])){
			$controller_class_name = $this->module_config['default_entity_controllers']['@all'];
		} else {
			throw new ControllerException(500, "Could not find a controller for entity class «{$entity_class}».");
		}

		if(!isset($this->module_config['controllers'][$controller_class_name])){
			throw new ControllerException(500, "Controller class for «{$controller_class_name}» not found.");
		}

		$controller_class = $this->module_config['controllers'][$controller_class_name];

		if(!is_subclass_of($controller_class, EntityController::class)){
			throw new ControllerException(500, "Controller «{$controller_class}» is not an EntityController.");
		}

		$this->controller_class = $controller_class;

		$this->options = $options;
	}


	public function get_name() : string {
		return $this->name;
	}


	public function has_option(string $name) : bool {
		return isset($this->options[$name]);
	}


	public function get_option(string $name) : mixed {
		return $this->options[$name] ?? null;
	}


	public function get_importance() : string {
		return $this->importance;
	}


	public function set_importance(string $importance) : void {
		$this->importance = $importance;
	}


	public function is_entity_controller() : bool {
		return isset($this->entity_class);
	}


	public function get_entity_class() : ?string {
		return $this->entity_class; // TODO maybe exception if not an EnttiyControlelr
	}


	public function create_controller() : Controller {
		$cls = $this->controller_class;
		return new $cls($this->importance);
	}
}
