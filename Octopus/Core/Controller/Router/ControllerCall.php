<?php
namespace Octopus\Core\Controller\Router;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Config\TempModuleNames;
use \Exception;

// TEMP: This entire class contains temporary code

class ControllerCall {
	private Request $request;
	private string $name;
	private string $controller_class;
	private ?string $entity_class;
	private array $options;


	function __construct(Request &$request) {
		$this->request = $request;
	}


	public function load_controller(string $name, array $options) : void {
		throw new Exception('Temporarily unimplemented.'); // TEMP
	}


	public function load_entity(string $name, array $options) : void {
		$this->name = $name; // TODO check this

		// TODO aliases
		// $entity_name = Substitution::resolve($name, $this->request);

		if(empty($options['class'])){
			throw new ControllerException(500, "Entity class missing in route «{$name}».");
		}

		$class = $options['class'];

		// if(!Config::has("Modules.$class", 'array'){
		// 	throw new ControllerException(500, "Entity class «{$class}» not found."); // TODO 404 for substituted
		// }
		//
		// if(!is_subclass_of($class, Entity::class)){
		// 	throw new ControllerException(500, "Entity class «{$class}» is not an entity class.");
		// }

		$this->entity_class = TempModuleNames::ENTITIES[$class]; // TEMP

		// $controller_class = Config::get("Modules.$class.controller");
		$controller_class = EntityController::class; // TEMP

		// if(!is_subclass_of($controller_class, Controller::class)){
		// 	throw new ControllerException(500, "Invalid controller «{$controller_class}» in module config «{$class}».");
		// }
		//
		// if(!is_subclass_of($controller_class, EntityController::class)){
		// 	throw new ControllerException(500, "Controller «{$controller_class}» is not an EntityController.");
		// }

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


	public function get_entity_class() : ?string {
		return $this->entity_class; // TODO maybe exception if not an EnttiyControlelr
	}


	public function create_controller() : Controller {
		$cls = $this->controller_class;
		return new $cls();
	}
}
