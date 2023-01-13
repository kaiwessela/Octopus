<?php
namespace Octopus\Modules\MarkdownText;
use Exception;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Modules\MarkdownText\MarkdownObject;

class MarkdownText extends PropertyAttribute {

	final public static function define(bool $is_editable = true) : MarkdownText {
		$attribute = new static(false, $is_editable);
		return $attribute;
	}


	final public function load(null|string|int|float $data) : void {
		$this->value = new MarkdownObject($data);
		$this->is_loaded = true;
	}


	final protected function _edit(mixed $input) : void {
		if(!is_null($input) && !is_string($input)){
			throw new Exception(); // TODO
		}

		$this->value = new MarkdownObject($data);
	}


	final public function get_push_value() : ?string {
		return $this->value->to_db();
	}


	final public function arrayify() : array {
		return $this->value->arrayify();
	}
}
?>