<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class AttributeNotAlterableException extends AttributeValueException {
	# inherited from AttributeValueException
	# public AttributeDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public Entity|Relationship $object;


	function __construct(AttributeDefinition $definition, Entity|Relationship $object, mixed $value) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $value;

		$this->object = $object;

		$this->message = "The attribute «{$this->name}» cannot be set to the value «"
			. var_dump($this->value)
			. "» because the attribute is not alterable. Current value: «"
			. var_dump($this->object->{$this->definition->name})
			. "».";
	}
}
?>
