<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Exception;

// TODO explainations

class IdentifierEquals extends Condition {
	protected IdentifierAttribute $attribute;
	protected string $value;


	function __construct(IdentifierAttribute $attribute, string $value) {
		parent::__construct();

		$this->attribute = $attribute;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_prefixed_db_column()} = :cond_{$index}";
		$this->values = ["cond_{$index}" => $this->value];

		return $index + 1;
	}


	public function get_attribute() : IdentifierAttribute {
		return $this->attribute;
	}
}
?>
