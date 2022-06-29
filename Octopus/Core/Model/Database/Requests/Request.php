<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Exception;

// TODO explainations

abstract class Request {
	protected string $table; # the name of the database table that contains the object data
	protected array $attributes;
	protected string $query;
	protected array $values;


	function __construct(string $table) {
		if(!preg_match('/^[a-z_]+$/', $table)){
			throw new Exception("Invalid table name: «{$table}».");
		}

		$this->table = $table;
		$this->attributes = [];
		$this->values = [];
	}


	final public function add(Attribute $attribute) : void {
		if(!$attribute->is_pullable()){
			throw new Exception("Attribute must be pullable.");
		}

		if($this->table !== $attribute->get_db_table()){
			throw new Exception("Property and Request db tables do not match.");
		}

		$this->attributes[$attribute->get_prefixed_db_column()] = $attribute;
	}


	final public function remove(Attribute $attribute) : void {
		unset($this->attributes[$attribute->get_prefixed_db_column()]);
	}


	abstract public function resolve() : void;


	final public function get_query() : string {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->query;
	}


	final public function get_values() : array {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->values;
	}


	final public function is_resolved() : bool {
		return isset($this->query);
	}
}
?>
