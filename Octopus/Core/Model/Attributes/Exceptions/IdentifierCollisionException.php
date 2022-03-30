<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;

class IdentifierCollisionException extends AttributeValueException {
	# inherited from AttributeValueException
	# public AttributeDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public Entity|Relationship $existing;


	function __construct(AttributeDefinition $definition, Entity|Relationship $existing) {
		$this->definition = $definition;
		$this->name = $this->definition->get_name();

		$this->existing = $existing;
		$this->value = $this->existing->{$this->definition->get_name()};

		$this->message = "An attempt to set the identifier «{$this->name}» to the value «{$this->value}» "
			. 'failed because that value is already used as identifier on another entity.';
	}
}
?>
