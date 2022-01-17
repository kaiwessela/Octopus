<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectList;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Cycle\Cycle;
use Exception;

// TODO explainations

abstract class Request {
	protected string $table; # the name of the database table that contains the object data
	protected array $properties;
	protected ?Condition $condition;

	protected string $query;
	protected ?array $values;

	protected Cycle $cycle;


	function __construct(string $table) {
		if(!preg_match('/^[a-z_]+$/', $table)){
			throw new Exception("Invalid table name: «{$table}».");
		}

		$this->cycle = new Cycle([
			['root', 'build'],
			['build', 'build'],
			['build', 'resolve']
		]);

		$this->cycle->start();

		$this->table = $table;
		$this->properties = [];
		$this->values = [];
		$this->condition = null;
	}


	final public function add_property(PropertyDefinition $definition) : void {
		if($this->table !== $definition->get_db_table()){
			throw new Exception("Property and Request db tables do not match.");
		}

		$this->properties["{$definition->get_db_table()}.{$definition->get_db_column()}"] = $definition;
	}


	final public function remove_property(PropertyDefinition $definition) : void {
		unset($this->properties["{$definition->get_db_table()}.{$definition->get_db_column()}"]);
	}


	abstract protected function resolve() : void;


	final public function get_query() : string {
		if(!$this->cycle->is_at('resolve')){
			$this->resolve();
		}

		return $this->query;
	}

	final public function get_values() : array {
		if(!$this->cycle->is_at('resolve')){
			$this->resolve();
		}

		return $this->values;
	}





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
