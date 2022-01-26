<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class MissingValueException extends AttributeValueException {
	# inherited from AttributeValueException
	# public AttributeDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(AttributeDefinition $definition) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = null;

		$this->message = "An attempt to set the attribute «{$this->name}» to an empty value failed "
			. 'because this attribute is not allowed to be empty';
	}
}
?>
