<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Exception;

class IDAttribute extends IdentifierAttribute {
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
	# public function get_push_value() : null|string|int|float;
	# public function resolve_pull_condition(mixed $option) : ?Condition;



	public static function define(bool $is_editable = false) : Attribute {
		return parent::define(is_editable:$is_editable);
	}


	public function generate() : void {
		if(!is_null($this->value)){
			throw new Exception('TODO; already set.');
		}

		$this->value = bin2hex(random_bytes(4)); # first generate 4 random bytes, then turn them into a hexadecimal string
	}


	public function load(null|string|int|float $data) : void {
		if(!is_string($data) && !is_null($data)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $data;
		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		if($input !== $this->value){
			throw new Exception('TODO; cannot edit.');
		}
	}
}
?>
