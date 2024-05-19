<?php
namespace Octopus\StandardAttributes;
use Exception;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\PropertyAttribute;

class FloatAttribute extends PropertyAttribute {

	public static function define(bool $is_required = false, bool $is_editable = true) : FloatAttribute {
		return new static($is_required, $is_editable);
	}


	public function load(null|string|int|float $data) : void {
		if(!is_float($data) && !is_null($data)){
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

		// TODO handle commas and dots

		if(!is_numeric($input)){
			throw new IllegalValueException($this, $input, 'not a number');
		}

		$this->value = (float) $input;
	}
}
?>
