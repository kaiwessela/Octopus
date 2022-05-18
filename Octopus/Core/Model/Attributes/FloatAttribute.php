<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;

class FloatAttribute extends Attribute {
	# inherited from Attribute:
	# protected Entity $parent;
	# protected string $name;
	# protected ?string $db_column;
	# protected mixed $value;
	# protected bool $editable;
	# protected bool $required;


	public static function define(bool $required = false, bool $editable = true) : FloatAttribute {
		$this->required = $required;
		$this->editable = $editable;
	}


	public function load(mixed $value) : void {
		if($value === ''){
			$this->value = null;
		} else {
			$this->value = (float) $value;
		}
	}


	public function edit(mixed $input) : void {
		if(is_null($input) || $input === ''){ # if the input is empty but the attribute is required to be set, throw an error // TODO (idk what excactly)
			if($this->is_required()){
				throw new MissingValueException($this);
			} else { # otherwise just set it to null
				$this->value = null;
			}
		} else if(!is_numeric($input)){
			throw new IllegalValueException($this, $input, 'not a number');
		}

		$new_value = (float) $input;

		if($new_value === $this->value){
			return;
		}

		if(is_null($this->value) && !$this->is_editable()){
			// NOTE: this means that the attribute is editable only once, but not only on the first edit. check if this is what we want
			throw new AttributeNotAlterableException($this, $this->parent, $new_value);
		}

		$this->value = $new_value;
	}
}
?>
