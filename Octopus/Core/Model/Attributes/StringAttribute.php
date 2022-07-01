<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Exception;

class StringAttribute extends PropertyAttribute {
	# inherited from PropertyAttribute
	# protected Entity|Relationship $parent;
	# protected string $name;
	# protected bool $is_loaded;
	# protected bool $is_required;
	# protected bool $is_editable;
	# protected bool $is_dirty;
	# protected mixed $value;

	protected ?int $min;
	protected ?int $max;
	protected ?string $pattern;


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



	public static function define(
		bool $is_required = false,
		bool $is_editable = true,
		?int $min = null,
		?int $max = null,
		?string $pattern = null
	) : StringAttribute {

		if(is_int($min)){
			if($min < 0){
				throw new Exception("min must not be negative.");
			}

			if($min > 0){
				$required = true;
			}
		}

		if(is_int($max)){
			if($max < 1){
				throw new Exception("max must be greater than 0.");
			}

			if($max < (int) $min){
				throw new Exception("max must not be less than min.");
			}
		}

		if(isset($pattern)){
			if(preg_match("/{$pattern}/", '') === false){
				throw new Exception("invalid pattern.");
			}
		}

		$attribute = new static($is_required, $is_editable);
		$attribute->min = $min;
		$attribute->max = $max;
		$attribute->pattern = $pattern;

		return $attribute;
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

			$escaped_input = null;
		} else {
			$escaped_input = htmlspecialchars($input); # escape html
		}


		# check whether the value has been altered
		# set the attribute value to the input
		if($escaped_input !== $this->value){
			if(!$this->is_editable()){
				throw new AttributeNotAlterableException($this, $escaped_input);
			}

			if(isset($this->min) && strlen($escaped_input) < $this->min){
				throw new IllegalValueException($this, $escaped_input, 'too short');
			}

			if(isset($this->max) && strlen($escaped_input) > $this->max){
				throw new IllegalValueException($this, $escaped_input, 'too long');
			}

			if(isset($this->pattern) && !preg_match("/{$this->pattern}/", $escaped_input)){
				throw new IllegalValueException($this, $escaped_input, 'pattern not matching');
			}

			$this->value = $escaped_input;
			$this->is_dirty = true;
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value;
	}


	// TODO
	// public function resolve_pull_condition(mixed $option) : ?Condition {
	//
	// }
}
?>
