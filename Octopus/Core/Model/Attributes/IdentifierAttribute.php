<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Exception;

class IdentifierAttribute extends PropertyAttribute {


	public static function define(bool $editable = true) : IdentifierAttribute {
		$attr = new IdentifierAttribute();
		$attr->required = true;
		$attr->editable = $editable;
		return $attr;
	}


	public function load(null|string|int|float $data) : void {
		if(!is_string($data) && !is_null($data)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $data;
		$this->edited = false;
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
			$this->edited = true;
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value;
	}
}
?>
