<?php
namespace Octopus\Modules\Identifiers;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\GeneratedIdentifierAttribute;
use \Exception;

final class ID extends GeneratedIdentifierAttribute {
	protected int $length;


	final public static function define(int $length = 8) : Attribute {
		if($length < 2 || $length % 2 !== 0){
			throw new Exception('Invalid length. length must be a positive and even integer.');
		}

		$attribute = new static(true, false);
		$attribute->length = $length;
		return $attribute;
	}


	final public function generator() : string|int|float {
		return bin2hex(random_bytes($this->length / 2));
	}

}
?>
