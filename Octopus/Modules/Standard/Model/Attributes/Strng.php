<?php
namespace Octopus\Modules\Standard\Model\Attributes;
use Exception;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Conditions\Equals;
use Octopus\Core\Model\Database\Conditions\InList;

class Strng extends PropertyAttribute {
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
	# public function get_push_value() : null|string|int|float;
	# public function arrayify() : null|string|int|float|bool|array;



	public static function define(
		bool $is_required = false,
		bool $is_editable = true,
		?int $min = null,
		?int $max = null,
		?string $pattern = null
	) : Strng {

		if(is_int($min)){
			if($min < 0){
				throw new Exception("min must not be negative.");
			}

			if($min > 0){
				$is_required = true;
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


	protected function _edit(mixed $input) : void {
		if(empty($input)){
			$this->value = null;
			return;
		}

		$input = htmlspecialchars($input); # escape html

		if(isset($this->min) && strlen($input) < $this->min){
			throw new IllegalValueException($this, $input, 'too short');
		}

		if(isset($this->max) && strlen($input) > $this->max){
			throw new IllegalValueException($this, $input, 'too long');
		}

		if(isset($this->pattern) && !preg_match("/{$this->pattern}/", $input)){
			throw new IllegalValueException($this, $input, 'pattern not matching');
		}

		$this->value = $input;
	}


	public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_array($option)){
			foreach($option as $opt){
				if(!is_string($opt)){
					throw new Exception('invalid condition.'); // TODO
				}
			}

			return new InList($this, $option);
		} else if(is_string($option)){
			return new Equals($this, $option);
		} else {
			throw new Exception('invalid condition.'); // TODO
		}
	}
}
?>
