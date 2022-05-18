<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Exception;

class StringAttribute extends Attribute {
	# inherited from Attribute:
	# protected Entity $parent;
	# protected string $name;
	# protected ?string $db_column;
	# protected mixed $value;
	# protected bool $editable;
	# protected bool $required;

	protected ?int $min;
	protected ?int $max;
	protected ?string $pattern;


	public static function define(bool $required = false, bool $editable = true, ?int $min = null, ?int $max = null, ?string $pattern = null) : StringAttribute {
		if(is_int($min) && $min > 0){
			$required = true;
		}

		if(isset($pattern)){
			if(preg_match("/{$pattern}/", '') === false){
				throw new Exception("Invalid pattern constraint regex in attribute «{$this->name}»: «{$pattern}».");
			}
		}

		$this->required = $required;
		$this->editable = $editable;
		$this->min = $min;
		$this->max = $max;
		$this->pattern = $pattern;
	}


	public function load(mixed $value) : void {
		if(empty($value)){
			$this->value = null;
		} else {
			$this->value = $value;
		}
	}


	public function edit(mixed $input) : void {
		if(empty($input)){ # if the input is empty but the attribute is required to be set, throw an error // TODO (idk what excactly)
			if($this->is_required()){
				throw new MissingValueException($this);
			} else { # otherwise just set it to null
				$this->value = null;
			}
		}

		$new_value = htmlspecialchars($input); # escape html

		if($new_value === $this->value){
			return;
		}

		if(is_null($this->value) && !$this->is_editable()){
			// NOTE: this means that the attribute is editable only once, but not only on the first edit. check if this is what we want
			throw new AttributeNotAlterableException($this, $this->parent, $new_value);
		}

		if(isset($this->min) && strlen($new_value) < $this->min){
			throw new IllegalValueException($this, $new_value, 'too short');
		}

		if(isset($this->max) && strlen($new_value) > $this->max){
			throw new IllegalValueException($this, $new_value, 'too long');
		}

		if(isset($this->pattern) && !preg_match("/{$this->pattern}/", $new_value)){
			throw new IllegalValueException($this, $new_value, 'pattern not matching');
		}

		$this->value = $new_value;
	}
}
?>
