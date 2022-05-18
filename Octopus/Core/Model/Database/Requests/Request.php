<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\FlowControl\Flow;
use Exception;

// TODO explainations

abstract class Request {
	protected string $table; # the name of the database table that contains the object data
	protected array $attributes;
	protected ?Condition $condition;

	protected string $query;
	protected ?array $values;

	protected Flow $flow;


	function __construct(string $table) {
		if(!preg_match('/^[a-z_]+$/', $table)){
			throw new Exception("Invalid table name: «{$table}».");
		}

		$this->flow = new Flow([
			['root', 'build'],
			['build', 'build'],
			['build', 'resolve']
		]);

		$this->flow->start();

		$this->table = $table;
		$this->attributes = [];
		// $this->values = [];
		$this->condition = null;
	}


	final public function add_attribute(Attribute $attribute) : void {
		if($this->table !== $attribute->get_db_table()){
			throw new Exception("Property and Request db tables do not match.");
		}

		$this->attributes[$attribute->get_full_db_column()] = $attribute;
	}


	final public function remove_attribute(Attribute $attribute) : void {
		unset($this->attributes[$attribute->get_full_db_column()]);
	}


	abstract protected function resolve() : void;


	final public function get_query() : string {
		if(!$this->flow->is_at('resolve')){
			$this->resolve();
		}

		return $this->query;
	}

	final public function get_values() : array {
		if(!$this->flow->is_at('resolve')){
			$this->resolve();
		}

		// return $this->values;

		$values = [];

		foreach($this->attributes as $name => $attribute){
			$values[$name] = $attribute->get_db_value();
		}

		// TODO condition values

		return $values;
	}


	final public function get_condition() : ?Condition { // FIXME this is a hotfix for CountRequest
		return $this->condition;
	}





	protected function validate_condition(?Condition $condition) : void {}

	public function set_condition(?Condition $condition) : void {
		$this->validate_condition($condition);

		$this->condition = $condition;
	}


	// DEPRECATED
	public function set_values(array $values) : void {
		$this->values = $values + $this->values; # values with the same key are overwritten, all others just stay
	}
}
?>
