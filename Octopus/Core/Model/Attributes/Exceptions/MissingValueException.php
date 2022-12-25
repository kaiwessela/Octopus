<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class MissingValueException extends AttributeValueException {
	# inherited from AttributeValueException
	# public Attribute $attribute;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(Attribute $attribute) {
		$this->attribute = $attribute;
		$this->value = null;

		$this->message = "An attempt to set the attribute «{$attribute->get_name()}» to an empty value failed "
			. 'because this attribute is not allowed to be empty';
	}
}
?>
