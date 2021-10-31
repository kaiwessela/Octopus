<?php # RelationObjectNotFoundException.php 2021-10-04 beta
namespace Blog\Core\Model\Properties\Exceptions;
use \Blog\Core\Model\Properties\PropertyDefinition;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;

class RelationObjectNotFoundException extends PropertyValueException { // TODO maybe rename to ObjectNotFoundException
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(PropertyDefinition $definition, string $identifier) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = $identifier;

		$this->message = "An attempt to set the property «{$this->name}» to an object of the class "
			. "«{$this->definition->class}» failed because no object of this class with the identifier "
			. "«{$this->value}» could be found.";
	}
}
?>
