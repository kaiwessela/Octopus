<?php
namespace Octopus\Core\Model\Properties\Exceptions;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelation;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;

class IdentifierCollisionException extends PropertyValueException {
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public DataObject|DataObjectRelation $existing;


	function __construct(PropertyDefinition $definition, DataObject|DataObjectRelation $existing) {
		$this->definition = $definition;
		$this->name = $this->definition->name;

		$this->existing = $existing;
		$this->value = $this->existing->{$this->definition->name};

		$this->message = "An attempt to set the identifier «{$this->name}» to the value «{$this->value}» "
			. 'failed because that value is already used as another object’s identifier.';
	}
}
?>
