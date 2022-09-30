<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class IllegalValueException extends AttributeValueException {
	# inherited from AttributeValueException
	# public Attribute $attribute;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(Attribute $attribute, mixed $value, string $message = '') {
		$this->attribute = $attribute;
		$this->value = $value;

		$this->message = "An attempt to set the attribute «{$attribute->get_name()}» to the value «"
			. var_export($this->value, true)
			. '» failed because that value does not fit the defined requirements for the attribute'
			. ($message === '') ? '.' : ": {$message}.";
	}
}
?>
