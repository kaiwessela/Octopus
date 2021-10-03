<?php
namespace Blog\Model\Properties\Exceptions;
use \Blog\Model\Properties\Exceptions\PropertyValueException;
use \Blog\Model\Abstracts\Traits\PropertyDefinition;

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

		$this->message = "An attempt to set the identifying property «{$this->name}» to the value «{$this->value}» "
			. 'failed because this identifier is already used on another object of the same class.';
	}
}
?>
