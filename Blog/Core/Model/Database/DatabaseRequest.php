<?php
namespace Octopus\Core\Model\Database;

abstract class DatabaseRequest {
	private string $object_class;
	private string $table;
	private string $column_prefix;
	private array $columns;
	private array $values;

	private ?RequestCondition $condition;


	function __construct(string $class) {
		if(is_subclass_of($class, DataObject::class)){
			$this->object_class = $class;
			$this->table = $class::DB_TABLE;
			$this->column_prefix = $class::DB_PREFIX;
		}

		$this->limit = null;
		$this->offset = null;
		$this->order_by = null;
		$this->order_desc = false;
	}


	abstract public function get_query() : string;
	abstract public function get_values() : array;
	abstract protected function check_condition(?RequestCondition $condition) : void;


	public function set_condition(?RequestCondition $condition) : void {
		$this->check_condition($condition);

		$this->condition = $condition;
	}
}
?>
