<?php # IdentifierMismatchException.php 2021-10-04 beta
namespace Blog\Core\Model\Properties\Exceptions;
use \Blog\Core\Model\DataObject;
use \Blog\Core\Model\DataObjectRelation;
use \Blog\Core\Model\Properties\PropertyDefinition;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;

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
