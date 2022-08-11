<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Attribute;
use \Exception;

class AttributeNotLoadedException extends Exception {
	public Attribute $attribute;


	function __construct(Attribute $attribute) {
		$this->attribute = $attribute;
		$this->message = "The attribute «{$attribute->get_name()}» must be loaded for this operation.";
	}
}
?>
