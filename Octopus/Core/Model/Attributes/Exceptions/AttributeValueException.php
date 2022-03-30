<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use Exception;

class AttributeValueException extends Exception {
	public AttributeDefinition $definition;
	public string $name; # attribute name
	public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(AttributeDefinition $definition, string $message, mixed $value = null) {
		$this->definition = $definition;
		$this->name = $this->definition->get_name();
		$this->value = $value;

		if($this->definition->type_is('custom')){
			$this->message = "An error occured trying to edit the custom attribute «{$this->name}»: $message";
		} else {
			$this->message = "An error occured trying to edit the attribute «{$this->name}»: $message";
		}
	}
}
?>
