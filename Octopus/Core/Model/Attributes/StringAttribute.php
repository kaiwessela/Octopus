<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Exception;

class StringAttribute extends Attribute {
	protected ?int $min;
	protected ?int $max;
	protected ?string $pattern;


	public static function define(
			bool $required = false,
			bool $editable = true,
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

		$attr = new StringAttribute();
		$attr->required = $required;
		$attr->editable = $editable;
		$attr->min = $min;
		$attr->max = $max;
		$attr->pattern = $pattern;
		return $attr;
	}


	public function load(null|string|int|float $value) : void {
		if(!is_string($value) && !is_null($value)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $value;
		$this->edited = false;
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
				throw new AttributeNotAlterableException($this, $this, $new_value); // TODO
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
			$this->edited = true;
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value;
	}
}
?>
