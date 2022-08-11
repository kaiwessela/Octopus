<?php
namespace Octopus\Modules\Identifiers;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;

class StringIdentifier extends IdentifierAttribute {

	public static function define(bool $is_editable = true) : Attribute {
		return new static(true, $is_editable);
	}


	protected function _edit(mixed $input) : void {
		if(empty($input)){
			$this->value = null;
			return;
		}

		if(!is_string($input)){
			throw new IllegalValueException($this, $input, 'not a string');
		}

		if(!preg_match('/^[a-z0-9-]{1,128}$/', $input)){
			throw new IllegalValueException($this, $input, 'pattern not matching');
		}

		$this->value = $input;
	}
}
?>
