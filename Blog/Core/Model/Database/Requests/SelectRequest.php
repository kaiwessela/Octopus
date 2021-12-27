<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use Exception;

class SelectRequest extends Request {
	# inherited from Request
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;
	# private ?array $values;
	# private ?Condition $condition;
	private array $joins;

	private ?int $limit;
	private ?int $offset;
	private ?PropertyDefinition $order_by;
	private bool $order_desc;


	function __construct(string $class, bool $disable_multiline_joins = false) {
		parent::__construct($class);

		$this->limit = null;
		$this->offset = null;
		$this->order_by = null;
		$this->order_desc = false;

		foreach($this->object_class::get_property_definitions() as $property => $definition){
			$column = "{$this->table}.{$column} AS {$this->column_prefix}_{$column}";

			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->columns[] = $column;
			} else if($definition->supclass_is(DataType::class)){
				$this->columns[] = $column;
			} else if($definition->supclass_is(DataObject::class)){
				$this->joins[] = new JoinRequest($definition->get_class(), $this->object_class);
			} else if($definition->supclass_is(DataObjectRelationList::class) && $disable_multiline_joins === false){
				$this->joins[] = new JoinRequest($definition->get_class(), $this->object_class);
			}
		}
	}


	public function get_query() : string {
		$columns = implode($this->columns, ', ');
		$table = $this->table;
		$joins = '';

		foreach($this->joins as $join){
			$columns .= ', ' . implode($join->columns, ', ');
			$joins .= $join->get_query();
		}

		$this->condition?->resolve();
		$this->set_values($this->condition?->get_values() ?? []);
		$where = $this->condition?->get_query() ?? '';

		$limit = '';
		$offset = '';

		if(!is_null($this->limit)){
			$limit = "LIMIT {$this->limit}";

			if(!is_null($this->offset)){
				$limit = " OFFSET {$this->offset}";
			}
		}

		$order = '';

		if(!is_null($this->order_by)){
			$order = "ORDER BY {$this->table}.{$this->order_by->get_name()}";

			if($this->order_desc === true){
				$order .= ' DESC';
			}
		}

		return "SELECT {$columns} FROM {$table} {$joins} {$where} {$limit} {$order}";
	}


	public function get_total_count_query() : string { // TEMP/TEST
		$table = $this->table;
		$joins = '';

		foreach($this->joins as $join){
			$joins .= $join->get_query();
		}

		$this->condition?->resolve();
		$this->set_values($this->condition?->get_values() ?? []);
		$where = $this->condition?->get_query() ?? '';

		return "SELECT COUNT(*) AS 'total' FROM {$table} {$joins} {$where}";
	}


	protected function validate_condition(?Condition $condition) : void {
		if(is_subclass_of($this->object_class, DataObject::class)){
			if(!$condition instanceof IdentifierCondition){
				// Error condition type not allowed
				// TODO improve condition type checking
				// maybe move to check right before get_query() and also check for illegally empty condition
				throw new Exception('illegal condition.');
			}
		}
	}


	public function set_limit(?int $limit) : void {
		if(is_int($limit) && $limit <= 0){
			throw new Exception('limit cannot be negative or zero.');
		}

		$this->limit = $limit;
	}


	public function set_offset(?int $offset) : void {
		if($offset < 0){
			throw new Exception('offset cannot be negative.');
		}

		$this->offset = $offset;
	}


	public function set_order(?PropertyDefinition $by, bool $desc = false) : void { // NOTE right now, the property must be from the base object, not joined
		$this->order_by = $by;
		$this->order_desc = $desc;
	}
}
?>
