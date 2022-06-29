<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;

abstract class Attribute {
	protected Entity|Relationship $parent;
	protected string $name;
	protected bool $is_loaded;
	protected bool $is_required;
	protected bool $is_editable;
	protected bool $is_dirty;
	protected mixed $value;


	public static function define(bool $is_required, bool $is_editable) : Attribute {
		$attribute = new self();

		$attribute->is_required = $is_required;
		$attribute->is_editable = $is_editable;

		return $attribute;
	}


	final public function bind(string $name, Entity|Relationship $parent) : void {
		$this->name = $name;
		$this->parent = &$parent;
		$this->value = null;
		$this->is_dirty = false;
		$this->is_loaded = false;
	}


	// abstract public function load($data) : void;


	abstract public function edit(mixed $input) : void;


	final public function is_loaded() : bool {
		return $this->is_loaded;
	}


	final public function is_required() : bool {
		return $this->is_required;
	}


	final public function is_editable() : bool {
		return $this->is_loaded() && ($this->is_editable || $this->parent->is_new());
	}


	final public function is_dirty() : bool {
		return $this->is_dirty;
	}


	public function is_pullable() : bool {
		return false;
	}


	public function is_joinable() : bool {
		return false;
	}


	final public function get_name() : string {
		return $this->name;
	}


	final public function get_db_table() : string {
		return $this->parent->get_db_table();
	}


	final public function get_prefixed_db_table() : string {
		return $this->parent->get_prefixed_db_table();
	}


	final public function &get_value() : mixed { // TODO maybe error if not loaded
		return $this->value;
	}


	public function is_empty() : bool {
		return $this->value === null;
	}


	abstract public function resolve_pull_condition(mixed $option) : ?Condition;


	abstract public function arrayify() : null|string|int|float|bool|array;

}
?>
