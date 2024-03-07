<?php
namespace Octopus\Modules\Standard\Model\Attributes;
use Exception;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\PropertyAttribute;

class Intgr extends PropertyAttribute {


	public static function define(bool $is_required = false, bool $is_editable = true) : Intgr {
		return new static($is_required, $is_editable);
	}


	public function load(null|string|int|float $data) : void {
		if(!is_int($data) && !is_null($data)){
			throw new Exception('Database value corrupted.');
		}

		$this->value = $data;
		$this->is_loaded = true;
	}


	public function _edit(mixed $input) : void {
		if(empty($input)){
			$this->value = null;
			return;
		}

		if(!is_numeric($value)){
			throw new IllegalValueException($this, $input, 'not a number');
		}

		if((int) $input == $input){
			throw new IllegalValueException($this, $input, 'not an integer');
		}

		$this->value = (int) $input;
	}
}
?>
