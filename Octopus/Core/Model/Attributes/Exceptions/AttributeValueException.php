<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use Exception;
use Octopus\Core\Model\Attribute;

class AttributeValueException extends Exception {
	public Attribute $attribute;
	public mixed $value;

	# inherited from Exception:
	# protected string $message;


	function __construct(Attribute $attribute, mixed $value, string $message) {
		$this->attribute = $attribute;
		$this->value = $value;
		$this->message = "An error occured trying to edit the attribute «{$attribute->get_name()}»: {$message}.";
	}
}
?>
