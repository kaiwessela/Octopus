<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Properties\PropertyDefinition;

class EqualsCondition extends Condition {
	protected PropertyDefinition $property;
	protected string|int|float|null $value;


	function __construct(PropertyDefinition $column, string|int|float|null $value) {
		parent::__construct();

		$this->property = $property;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->property->get_db_table()}.{$this->property->get_db_column()} = :cond_{$index}";
		$this->values = ["cond_{$index}" => $this->value];

		return $index + 1;
	}
}
