<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\PullableAttributes;

abstract class PropertyAttribute extends Attribute {
	# inherited from Attribute
	# protected Entity|Relationship $parent;
	# protected string $name;
	# protected bool $is_loaded;
	# protected bool $is_required;
	# protected bool $is_editable;
	# protected bool $is_dirty;
	# protected mixed $value;


	# ---> Attribute
	# abstract public static function define() : Attribute;
	# final public function bind(string $name, Entity|Relationship $parent) : void;
	# abstract public function edit(mixed $input) : void;
	# final public function is_loaded() : bool;
	# final public function is_required() : bool;
	# final public function is_editable() : bool;
	# final public function is_dirty() : bool;
	# public function is_joinable() : bool;
	# final public function get_name() : string;
	# final public function get_db_table() : string;
	# final public function get_prefixed_db_table() : string;
	# final public function &get_value() : mixed;
	# public function is_empty() : bool;
	# abstract public function resolve_pull_condition(mixed $option) : ?Condition;

	use PullableAttributes;
	# final public function is_pullable();
	# final public function get_prefixed_db_column() : string;
	# final public function get_result_column() : string;
	# abstract public function get_push_value() : null|string|int|float;



	abstract public function load(null|string|int|float $data) : void;


	final public function get_db_column() : string {
		return $this->name;
	}
}
?>
