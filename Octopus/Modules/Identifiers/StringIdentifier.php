<?php
namespace Octopus\Modules\Identifiers;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;

class StringIdentifier extends IdentifierAttribute {

	public static function define(bool $is_editable = true) : Attribute {
		return new static(true, $is_editable);
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
			$this->set_dirty();
		}
	}

}
?>
