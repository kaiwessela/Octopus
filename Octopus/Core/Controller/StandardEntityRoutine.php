<?php
namespace Octopus\Core\Controller;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Router\URLSubstitution;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\EntityList;

class StandardEntityRoutine extends StandardRoutine implements Routine {
	public Entity|EntityList $object;

	protected string $action;
	protected string $class;
	protected null|string|int $identifier;
	protected ?string $identify_by;
	protected array $include_attributes;
	protected array $order_by;
	protected ?int $limit;
	protected ?int $offset;
	protected array $conditions;


	public function load(
		string $action, # create | pull | pull-list | edit | delete
		string $class,
		null|string|int $identifier = null,
		?string $identify_by = null,
		array $include_attributes = [],
		array $order_by = [],
		?int $limit = null,
		?int $offset = null,
		array $conditions = [],
	) : void {
		if(!in_array($action, ['create', 'pull', 'list', 'edit', 'delete'])){
			throw new Exception("Invalid action «{$action}».");
		}

		$this->action = $action;

		if(!is_subclass_of($class, Entity::class)){
			throw new Exception("Class «{$class}» is not a subclass of Entity.");
		}

		if(!defined("{$class}::X_STANDARD_ENTITY_ROUTINE")){
			throw new Exception("Class «{$class}» is not compatible with StandardEntityRoutine.");
		}

		$this->class = $class;

		$this->identifier = $identifier;
		$this->identify_by = $identify_by;
		$this->include_attributes = $include_attributes;
		$this->order_by = $order_by;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->conditions = $conditions;
	}


	public function run() : void {
		// $this->check_environment($this->environment);
		$class = $this->class;

		if($this->action === 'list'){
			$this->object = $class::list($this->environment->get_db());
			$this->object->pull($this->limit, $this->offset, $this->include_attributes, $this->conditions, $this->order_by);
		} else {
			$this->object = new $class($this->environment->get_db());
			
			if($this->action === 'create'){
				$this->object->create();
				$this->object->receive_input($this->environment->get_request()->get_content());
				$this->object->push();
			} else {
				$this->object->pull($this->identifier, $this->identify_by, $this->include_attributes);

				if($this->action === 'edit'){
					$this->object->receive_input($this->environment->get_request()->get_content());
					$this->object->push();
				} else if($this->action === 'delete'){
					$this->object->delete();
				}
			}
		}

	}

}