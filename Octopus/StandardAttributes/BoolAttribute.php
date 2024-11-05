<?php
namespace Octopus\StandardAttributes;
use Exception;
use Octopus\Core\Model\Attributes\PropertyAttribute;

class BoolAttribute extends PropertyAttribute {


	public static function define(bool $is_required = false, bool $is_editable = true) : BoolAttribute {
		return new static($is_required, $is_editable);
	}


	public function load(null|string|int|float $data) : void {
		$this->value = $data;
		$this->is_loaded = true;
	}


	public function _edit(mixed $input) : void {
		if(empty($input) || $input === 'null' || $input === 'unset'){
			$this->value = null;
			return;
		}

		if($input === true || $input === 1 || $input === 'true'){
			$this->value = true;
		} else if($input === false || $input === 0 || $input === 'false'){
			$this->value = false;
		} else {
			throw new IllegalValueException($this, $input, 'not a valid boolean and not convertible');
		}
	}
}
?>
