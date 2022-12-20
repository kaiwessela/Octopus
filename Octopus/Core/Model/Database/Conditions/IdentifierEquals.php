<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Exception;

# An IdentifierEquals condition compares any identifier attribute (column) value to a single given comparison value
# using the EQUAL (=) operator.
# The comparison value can be any scalar value.
# This is functionally equivalent to the Equals condition, but only works with IdentifierAttributes. Because some
# request types only work on single rows, using this condition is an easy way to ensure that only a single, uniquely
# identified row is being modified.

class IdentifierEquals extends Condition {
	protected IdentifierAttribute $attribute;
	protected string $value;


	function __construct(IdentifierAttribute $attribute, string $value) {
		parent::__construct();

		$this->attribute = $attribute;
		$this->value = $value;
	}


	protected function simplified_resolve() : string {
		return "{$this->attribute->get_prefixed_db_column()} = {$this->substitute($this->value)}";
	}


	# used by SelectRequest->selects_single_object()
	public function get_attribute() : IdentifierAttribute {
		return $this->attribute;
	}
}
?>
