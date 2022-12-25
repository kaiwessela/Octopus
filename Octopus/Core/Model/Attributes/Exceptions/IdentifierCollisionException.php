<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Relationship;

class IdentifierCollisionException extends AttributeValueException {
	# inherited from AttributeValueException
	# public Attribute $attribute;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;

	public Entity|Relationship $existing;


	function __construct(Attribute $attribute, Entity|Relationship $existing) {
		$this->attribute = $attribute;
		$this->existing = $existing;

		$name = $attribute->get_name();
		$this->value = $this->existing->$name;

		$this->message = "An attempt to set the identifier «{$attribute->get_name()}» to the value «{$this->value}» "
			. 'failed because that value is already used as identifier on another entity.';
	}
}
?>
