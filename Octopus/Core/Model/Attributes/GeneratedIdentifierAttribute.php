<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Exception;

abstract class GeneratedIdentifierAttribute extends IdentifierAttribute {
	# inherited from PropertyAttribute
	# protected Entity|Relationship $parent;
	# protected string $name;
	# protected bool $is_loaded;
	# protected bool $is_required;
	# protected bool $is_editable;
	# protected bool $is_dirty;
	# protected mixed $value;


	# ---> Attribute
	# final public function bind(string $name, Entity|Relationship $parent) : void;
	# final public function is_loaded() : bool;
	# final public function is_required() : bool;
	# final public function is_editable() : bool;
	# final public function is_dirty() : bool;
	# final public function set_clean() : void;
	# public function is_joinable() : bool;
	# final public function get_name() : string;
	# final public function get_db_table() : string;
	# final public function get_prefixed_db_table() : string;
	# final public function &get_value() : mixed;
	# public function is_empty() : bool;

	# ---> PullableAttributes
	# final public function is_pullable();
	# final public function get_prefixed_db_column() : string;
	# final public function get_result_column() : string;

	# ---> PropertyAttribute
	# final public function get_db_column() : string;
	# public function arrayify() : null|string|int|float|bool|array;

	# ---> IdentifierAttribute
	# final public function load(null|string|int|float $data) : void;
	# final public function get_push_value() : null|string|int|float;
	# final public function resolve_pull_condition(mixed $option) : ?Condition;



	public static function define() : Attribute {
		return new static(true, false);
	}


	final public function generate() : void {
		if(!$this->is_editable()){
			throw new AttributeNotAlterableException($this, null);
		}

		$this->value = $this->generator();
		$this->is_dirty = true;
	}


	final protected function _edit(mixed $input) : void {
		throw new AttributeNotAlterableException($this, $input);
	}


	abstract protected function generator() : string|int|float;
}
