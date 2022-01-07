<?php
namespace Octopus\Core\Model\Properties\Exceptions;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;

class IllegalValueException extends PropertyValueException {
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(PropertyDefinition $definition, mixed $value, string $message = '') {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $value;

		$this->message = "An attempt to set the property «{$this->name}» to the value «"
			. var_export($this->value, true)
			. '» failed because that value does not fit the defined requirements for the property'
			. ($message === '') ? '.' : ": {$message}.";
	}
}
?>
