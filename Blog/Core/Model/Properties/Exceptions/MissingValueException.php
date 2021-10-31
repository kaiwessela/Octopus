<?php # MissingValueException.php 2021-10-04 beta
namespace Blog\Core\Model\Properties\Exceptions;
use \Blog\Core\Model\Properties\PropertyDefinition;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;

class MissingValueException extends PropertyValueException {
	# inherited from PropertyValueException:
	# public PropertyDefinition $definition;
	# public string $name;
	# public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(PropertyDefinition $definition) {
		$this->definition = $definition;
		$this->name = $this->definition->name;
		$this->value = null;

		$this->message = "An attempt to set the property «{$this->name}» to an empty value failed "
			. 'because this property is not allowed to be empty';
	}
}
?>
