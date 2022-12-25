<?php
namespace Octopus\Core\Model\Database\Conditions;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Database\Condition;

# An Equals condition compares any attribute (column) value to a single given comparison value
# using the EQUAL (=) operator.
# The comparison value can be any scalar value.

class Equals extends Condition {
	protected Attribute $attribute;
	protected string|int|float|null $value;


	function __construct(Attribute $attribute, string|int|float|null $value) {
		parent::__construct();

		$this->attribute = $attribute;
		$this->value = $value;
	}


	protected function simplified_resolve() : string {
		return "{$this->attribute->get_prefixed_db_column()} = {$this->substitute($this->value)}";
	}
}
