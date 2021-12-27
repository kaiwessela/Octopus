<?php
namespace Octopus\Core\Model\Properties\Exceptions;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelation;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;

class PropertyNotAlterableException extends PropertyValueException {
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public DataObject|DataObjectRelation $object;


	function __construct(PropertyDefinition $definition, DataObject|DataObjectRelation $object, mixed $value) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $value;

		$this->object = $object;

		$this->message = "The property «{$this->name}» cannot be set to the value «"
			. var_dump($this->value)
			. "» because the property is not alterable. Current value: «"
			. var_dump($this->object->{$this->definition->name})
			. "».";
	}
}
?>
