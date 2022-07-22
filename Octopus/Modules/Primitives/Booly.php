<?php
namespace Octopus\Modules\Primitives;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Exception;

class Booly extends PropertyAttribute {


	public static function define(bool $is_required = false, bool $is_editable = true) : Booly {
		return new static($is_required, $is_editable);
	}


	public function load(null|string|int|float $data) : void {
		$this->value = $data;
		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		throw new Exception('not yet');
	}
}
?>
