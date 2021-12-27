<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Exceptions\InvalidModelCallException;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectList;

abstract class Request {
	private string $object_class; # the class name of the DataObject(Relation); lists are replaced by their corresponding single object
	private string $table; # the name of the database table that contains the object data
	private string $column_prefix;
	private array $columns;
	private ?array $values;
	private ?Condition $condition;


	function __construct(string $class) {
		if(is_subclass_of($class, DataObject::class)){
			$this->object_class = $class;
		} else if(is_subclass_of($class, DataObjectList::class)){
			$this->object_class = $class::OBJECT_CLASS;
		} else {
			throw new InvalidModelCallException("Argument class: the specified class «{$class}» is not supported.");
		}

		$this->table = $this->object_class::DB_TABLE;
		$this->column_prefix = $this->object_class::DB_PREFIX;
	}


	abstract public function get_query() : string;
	abstract public function get_values() : array;
	abstract protected function validate_condition(?Condition $condition) : void;


	public function set_condition(?Condition $condition) : void {
		$this->validate_condition($condition);

		$this->condition = $condition;
	}


	public function set_values(array $values) : void {
		$this->values = $values + $this->values; # values with the same key are overwritten, all others just stay
	}
}
?>
