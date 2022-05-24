<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Attribute;
use \Exception;

abstract class Attribute {
	protected string|Entity $parent;
	protected string $name;
	protected bool $required;
	protected bool $editable;
	protected bool $loaded;
	protected bool $edited;
	protected mixed $value;


	// abstract public static function define() : Attribute;


	final public function init(string $name, string $parent_class) : void {
		$this->name = $name;

		if(!class_exists($parent_class) || !is_subclass_of($parent_class, Entity::class)){
			throw new Exception("Invalid parent class: «{$parent_class}».");
		}

		// TODO check DB_TABLE

		$this->parent = $parent_class;
		$this->loaded = false;
	}


	final public function bind(Entity &$parent) : void {
		$this->parent = &$parent;
		$this->value = null;
		$this->edited = false;
	}


	// abstract public function load(mixed $data) : void;


	abstract public function edit(mixed $input) : void;


	final public function has_been_edited() : bool {
		return $this->edited;
	}


	final public function is_bound() : bool {
		return !is_string($this->parent);
	}


	final public function is_loaded() : bool {
		return $this->loaded;
	}


	final public function is_required() : bool {
		return $this->required;
	}


	final public function is_editable() : bool {
		if($this->is_bound()){
			return $this->editable;
		} else {
			return $this->is_loaded() && ($this->editable || $this->parent->is_new());
		}
	}


	final public function get_name() : string {
		return $this->name;
	}


	final public function get_db_table() : string {
		return $this->parent::DB_TABLE;
	}


	final public function get_db_prefix() : string {
		return $this->parent::DB_PREFIX;
	}


	abstract public function get_db_column() : string;


	final public function get_full_db_column() : string {
		return "{$this->get_db_table()}.{$this->get_db_column()}";
	}


	final public function get_prefixed_db_column() : string {
		return "{$this->get_db_prefix()}_{$this->get_db_column()}";
	}


	final public function get_value() : mixed {
		return $this->value;
	}


	public function is_empty() : bool {
		return $this->value === null;
	}


	abstract public function get_push_value() : null|string|int|float;

}
?>
