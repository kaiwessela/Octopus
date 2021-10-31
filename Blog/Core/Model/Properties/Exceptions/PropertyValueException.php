<?php # PropertyValueException.php 2021-10-04 beta
namespace Blog\Core\Model\Properties\Exceptions;
use \Blog\Core\Model\Properties\PropertyDefinition;
use Exception;

class PropertyValueException extends Exception {
	public PropertyDefinition $definition;
	public string $name; # property name
	public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(PropertyDefinition $definition, string $message, mixed $value = null) {
		$this->definition = $definition
		$this->name = $this->definition->name;
		$this->value = $value;

		if($this->definition->type_is_custom()){
			$this->message = "An error occured trying to edit the custom property «{$this->name}»: $message";
		} else {
			$this->message = "An unknown error occured trying to edit the property «{$this->name}»: $message";
		}
	}
}
?>
