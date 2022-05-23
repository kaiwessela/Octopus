<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Exception;

class IDAttribute extends IdentifierAttribute {


	public static function define(bool $editable = false) : IDAttribute {
		$attr = new IDAttribute();
		$attr->required = true;
		$attr->editable = false;
		return $attr;
	}


	public function load(null|string|int|float $value) : void {
		if(!is_string($value) && !is_null($value)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $value;
		$this->edited = false;
	}


	public function edit(mixed $input) : void {
		if($input !== $this->value){
			throw new Exception('TODO; cannot edit.');
		}
	}


	public function generate() : void {
		if(!is_null($this->value)){
			throw new Exception('TODO; already set.');
		}

		$this->value = bin2hex(random_bytes(4)); # first generate 4 random bytes, then turn them into a hexadecimal string
	}
}
?>
