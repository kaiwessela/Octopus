<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use Exception;

class SelectRequest extends Request {
	protected ?int $limit;
	protected ?int $offset;
	protected ?PropertyDefinition $order_by;
	protected bool $order_desc;

	use SelectAndJoin;

	# for SelectAndJoin
	protected array $columns;
	protected array $joins;


	function __construct(string $table) {
		parent::__construct($table);

		$this->joins = [];
		$this->columns = [];

		$this->limit = null;
		$this->offset = null;
		$this->order_by = null;
		$this->order_desc = false;
	}


	public function set_limit(?int $limit) : void {
		$this->cycle->step('build');

		if(is_int($limit) && $limit <= 0){
			throw new Exception('limit cannot be negative or zero.');
		}

		$this->limit = $limit;
	}


	public function set_offset(?int $offset) : void {
		$this->cycle->step('build');

		if(is_int($offset) && $offset < 0){
			throw new Exception('offset cannot be negative.');
		}

		$this->offset = $offset;
	}


	public function set_order(?PropertyDefinition $by, bool $desc = false) : void {
		$this->cycle->step('build');

		$this->order_by = $by;
		$this->order_desc = $desc;
	}


	protected function resolve() : void {
		$this->cycle->step('resolve');

		$this->query = 'SELECT'.PHP_EOL;

		$join_str = '';

		foreach($this->properties as $property){
			$this->columns[] = static::create_column_string($property);
		}

		foreach($this->joins as $join){
			$this->columns = array_merge($this->columns, $join->get_columns());
			$join_str .= $join->get_query();
		}

		$this->query .= implode(','.PHP_EOL, $this->columns).PHP_EOL;

		$this->query .= "FROM {$this->table}".PHP_EOL;
		$this->query .= $join_str;

		if(!is_null($this->condition)){
			$this->query .= "WHERE {$this->condition->get_query()}".PHP_EOL;
			$this->set_values($this->condition->get_values());
		}

		if(!is_null($this->limit)){
			$this->query .= "LIMIT {$this->limit}";

			if(!is_null($this->offset)){
				$this->query .= " OFFSET {$this->offset}";
			}

			$this->query .= PHP_EOL;
		}

		if(!is_null($this->order_by)){
			$this->query .= "ORDER BY {$this->order_by->get_db_table()}.{$this->order_by->get_db_column()}";

			if($this->order_desc === true){
				$this->query .= ' DESC';
			}
		}
	}


	protected function validate_condition(?Condition $condition) : void {
		/*
		if(is_subclass_of($this->object_class, DataObject::class)){
			if(!$condition instanceof IdentifierCondition){
				// Error condition type not allowed
				// TODO improve condition type checking
				// maybe move to check right before get_query() and also check for illegally empty condition
				throw new Exception('illegal condition.');
			}
		}
		*/
	}
}
?>
