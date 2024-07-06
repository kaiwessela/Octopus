<?php
namespace Octopus\Modules\Web;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Router\URLSubstitution;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\EntityList;
use Octopus\Modules\Web\WebEnvironment;
use Octopus\Modules\Web\WebRoutine;

class RestEntityRoutine extends WebRoutine {
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


	function __construct(
		string $action, # create | pull | pull-list | edit | delete
		string $class,
		null|string|int $identifier = null,
		?string $identify_by = null,
		array $include_attributes = [],
		array $order_by = [],
		?int $limit = null,
		?int $offset = null,
		array $conditions = [],
	) {
		if(!in_array($action, ['create', 'pull', 'list', 'edit', 'delete'])){
			throw new Exception("Invalid action «{$action}».");
		}

		$this->action = $action;

		if(!is_subclass_of($class, Entity::class)){
			throw new Exception("Class «{$class}» is not a subclass of Entity.");
		}

		if(!defined("{$class}::X_STANDARD_ENTITY_ROUTINE")){
			throw new Exception("Class «{$class}» is not compatible with RestEntityRoutine.");
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


	public static function create_from_route(array $options, WebEnvironment $env) : self {
		return new self(
			action: $options['action'],
			class: $options['entity'],
			identifier: URLSubstitution::replace($options['identifier'] ?? null, $env->get_request()),
			identify_by: URLSubstitution::replace($options['identify_by'] ?? null, $env->get_request()),
			include_attributes: $options['include_attributes'] ?? [],
			order_by: $options['order_by'] ?? [],
			limit: $options['limit'] ?? null,
			offset: $options['offset'] ?? null,
			conditions: $options['conditions'] ?? []
		);
	}


	public function run(Environment &$env) : void {
		$this->check_environment($env);
		$class = $this->class;

		if($this->action === 'list'){
			$this->object = $class::list($env->get_db());
			$this->object->pull($this->limit, $this->offset, $this->include_attributes, $this->conditions, $this->order_by);
		} else {
			$this->object = new $class($env->get_db());
			
			if($this->action === 'create'){
				$this->object->create();
				$this->object->receive_input($this->request->get_data());
				$this->object->push();
			} else {
				$this->object->pull($this->identifier, $this->identify_by, $this->include_attributes);

				if($this->action === 'edit'){
					$this->object->receive_input($this->request->get_data());
					$this->object->push();
				} else if($this->action === 'delete'){
					$this->object->delete();
				}
			}
		}

	}

}