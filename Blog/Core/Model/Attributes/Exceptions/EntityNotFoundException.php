<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class EntityNotFoundException extends AttributeValueException {
	# inherited from AttributeValueException
	# public AttributeDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(AttributeDefinition $definition, string $identifier) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $identifier;

		$this->message = "An attempt to set the attribute «{$this->name}» to an object of the class "
			. "«{$this->definition->class}» failed because no object of this class with the identifier "
			. "«{$this->value}» could be found.";
	}
}
?>
