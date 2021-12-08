<?php
namespace Octopus\Core\Model\Database\Requests;

class SelectRequest extends DatabaseRequest {
	# inherited from DatabaseRequest
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;
	private array $joins;

	private ?int $limit;
	private ?int $offset;
	private ?PropertyDefinition $order_by;
	private bool $order_desc;


	function __construct(string $class) {
		parent::__construct($class);

		foreach($class::PROPERTIES as $property => $definition){
			$def = new PropertyDefinition($property, $definition);
			$column = "{$this->table}.{$column} AS {$this->column_prefix}_{$column}";

			if($def->type_is('primitive') || $def->type_is('identifier')){
				$this->columns[] = $column;
			} else if($def->type_is('object')){
				if($def->supclass_is(DataType::class)){
					$this->columns[] = $column;
				} else if($def->supclass_is(DataObject::class)){
					$this->joins[] = new JoinRequest($def->get_class(), $this->object_class);
				} else if($def->supclass_is(DataObjectRelationList::class)){
					$this->joins[] = new JoinRequest($def->get_class());
				}
			}
		}
	}


	public function get_query() : string {
		$query = [];

		$query[] = 'SELECT';
		$query[] = $this->get_column_list();
		$query[] = 'FROM';
		$query[] = $this->table;

		foreach($joins as $join){
			$query[] = $join->get_query();
		}

		if(!is_null($this->condition)){
			$query[] = 'WHERE';

			$condition = $this->condition->resolve(0);
			$this->values = $condition['values'];

			$query[] = $condition['query'];
		}

		if(!is_null($this->limit)){
			$query[] = "LIMIT $this->limit";
		}

		if(!is_null($this->offset)){
			$query[] = "OFFSET $this->offset";
		}

		if(!is_null($this->order_by)){
			$query[] = 'ORDER BY';
			$query[] = $this->table.'.'.$this->order_by->name;

			if($this->order_desc === true){
				$query[] = 'DESC';
			}
		}

		return implode($query, ' ');
	}


	public function get_column_list() : array {
		$column_list = implode($this->columns, ', ');

		foreach($this->joins as $join){
			$column_list .= ', ' . $join->get_column_list();
		}

		return $column_list;
	}


	protected function check_condition(?RequestCondition $condition) : void {
		if(is_subclass_of($this->object_class, DataObject::class)){
			if(!$condition instanceof IdentifierCondition){
				// Error condition type not allowed
				// TODO improve condition type checking
				// maybe move to check right before get_query() and also check for illegally empty condition
			}
		} else if(is_subclass_of($this->object_class, DataObjectList::class)){

		}
	}


	public function set_limit(?int $limit) : void {
		if(is_null($limit) && is_int($this->offset)){
			throw new Exception('limit cannot be unset while offset being set. unset offset first.');
		}

		if(is_int($limit) && $limit <= 0){
			throw new Exception('limit cannot be negative or zero.');
		}

		$this->limit = $limit;
	}


	public function set_offset(?int $offset) : void {
		if(is_null($this->limit)){
			throw new Exception('offset cannot be set without a limit. set limit first.');
		}

		if($offset < 0){
			throw new Exception('offset cannot be negative.');
		}

		$this->offset = $offset;
	}


	public function set_order(?PropertyDefinition $by, bool $desc = false) : void {
		$this->order_by = $by;
		$this->order_desc = $desc;
	}
}
?>
