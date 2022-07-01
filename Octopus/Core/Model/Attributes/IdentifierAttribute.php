<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Exception;

class IdentifierAttribute extends PropertyAttribute {
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



	public static function define(bool $is_editable = true) : Attribute {
		return new static(true, $is_editable);
	}


	public function load(null|string|int|float $data) : void {
		if(!is_string($data) && !is_null($data)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $data;
		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		if(empty($input)){ # if the input is empty but the attribute is required to be set, throw an error
			if($this->is_required()){
				throw new MissingValueException($this);
			}
		}

		if($input !== $this->value){
			if(!$this->is_editable()){
				throw new AttributeNotAlterableException($this, $this, $new_value); // TODO
			}

			if(is_string($input) && !preg_match('/^[a-z0-9-]{1,128}$/', $input)){
				throw new IllegalValueException($this, $input, 'pattern not matching');
			}

			$this->value = $input;
			$this->is_dirty = true;
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value;
	}


	public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_string($option)){
			return new IdentifierEqualsCondition($this, $option);
		} else {
			throw new Exception(); // TODO
		}
	}
}
?>
