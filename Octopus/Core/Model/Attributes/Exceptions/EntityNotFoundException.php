<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class EntityNotFoundException extends AttributeValueException {
	# inherited from AttributeValueException
	# public Attribute $attribute;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(Attribute $attribute, string $identifier) {
		$this->attribute = $attribute;
		$this->value = $identifier;

		$this->message = "An attempt to set the attribute «{$attribute->get_name()}» to an entity of the class "
			. "«{$attribute->get_class()}» failed because no entity of this class with the identifier "
			. "«{$identifier}» could be found.";
	}
}
?>
