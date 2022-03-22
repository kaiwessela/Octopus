<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\AttributeDefinition;

// TODO explainations

class Equals extends Condition {
	protected AttributeDefinition $attribute;
	protected string|int|float|null $value;


	function __construct(AttributeDefinition $column, string|int|float|null $value) {
		parent::__construct();

		$this->attribute = $column;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_db_table()}.{$this->attribute->get_db_column()} = :cond_{$index}";
		$this->values = ["cond_{$index}" => $this->value];

		return $index + 1;
	}
}
