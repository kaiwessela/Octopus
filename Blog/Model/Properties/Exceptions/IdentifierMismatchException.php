<?php
namespace Blog\Model\Properties\Exceptions;
use \Blog\Model\Properties\Exceptions\PropertyValueException;
use \Blog\Model\Abstracts\Traits\PropertyDefinition;

class IdentifierMismatchException extends PropertyValueException {
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public DataObject|DataObjectRelation $object;


	function __construct(PropertyDefinition $definition, DataObject|DataObjectRelation $object, string $value) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $value;

		$this->object = $object;

		$this->message = "The received value «{$this->value}» for the identifying property «{$this->name}» "
			. "does not equal the present value «{$this->object->{$this->definition->name}}».";
	}
}
?>
