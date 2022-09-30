<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class AttributeNotAlterableException extends AttributeValueException {
	# inherited from AttributeValueException
	# public Attribute $attribute;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(Attribute $attribute, mixed $value = null) { // TODO remove value
		$this->attribute = $attribute;
		$this->value = $value;

		$this->message = "The attribute «{$attribute->get_name()}» cannot be set to the value «"
			. var_export($value)
			. "» because the attribute is not alterable. Current value: «"
			. var_export($attribute->arrayify())
			. "».";
	}
}
?>
