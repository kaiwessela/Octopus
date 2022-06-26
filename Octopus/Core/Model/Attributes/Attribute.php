<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Attributes\Attribute;
use \Exception;

abstract class Attribute {
	protected Entity|Relationship $parent;
	protected string $name;
	protected bool $required;
	protected bool $editable;
	protected bool $loaded;
	protected bool $edited;
	protected mixed $value;


	// abstract public static function define() : Attribute;


	public function bind(string $name, Entity|Relationship $parent) : void {
		$this->name = $name;
		$this->parent = &$parent;
		$this->value = null;
		$this->edited = false;
		$this->loaded = false;

		// TODO check DB_TABLE
	}


	// abstract public function load(mixed $data) : void;


	abstract public function edit(mixed $input) : void;


	final public function has_been_edited() : bool {
		return $this->edited;
	}


	final public function is_loaded() : bool {
		return $this->loaded;
	}


	final public function is_required() : bool {
		return $this->required;
	}


	final public function is_editable() : bool {
		return $this->is_loaded() && ($this->editable || $this->parent->is_new());
	}


	abstract public function is_pullable() : bool;

	abstract public function is_joinable() : bool;


	final public function get_name() : string {
		return $this->name;
	}


	final public function get_db_table() : string {
		return $this->parent::DB_TABLE;
	}


	final public function get_db_table_alias() : string {
		if(isset($this->parent->db_alias)){
			return $this->parent->db_alias;
		} else {
			return $this->get_db_table();
		}
	}


	abstract public function get_db_column() : string;


	final public function get_full_db_column() : string {
		return "`{$this->get_db_table_alias()}`.`{$this->get_db_column()}`";
	}


	final public function get_prefixed_db_column() : string {
		return "{$this->get_db_table_alias()}.{$this->get_db_column()}";
	}


	final public function &get_value() : mixed {
		return $this->value;
	}


	public function is_empty() : bool {
		return $this->value === null;
	}


	abstract public function get_push_value() : null|string|int|float;


	abstract public function resolve_condition(mixed $option) : ?Condition;

}
?>
