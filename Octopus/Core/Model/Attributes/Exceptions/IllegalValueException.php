<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class IllegalValueException extends AttributeValueException {
	# inherited from AttributeValueException
	# public AttributeDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(AttributeDefinition $definition, mixed $value, string $message = '') {
		$this->definition = $definition;
		$this->name = $this->definition->get_name();
		$this->value = $value;

		$this->message = "An attempt to set the attribute «{$this->name}» to the value «"
			. var_export($this->value, true)
			. '» failed because that value does not fit the defined requirements for the attribute'
			. ($message === '') ? '.' : ": {$message}.";
	}
}
?>
