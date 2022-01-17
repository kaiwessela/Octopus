<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Properties\PropertyDefinition;

// TODO explainations

class InList extends Condition {
	protected PropertyDefinition $property;
	protected array $vals;


	function __construct(PropertyDefinition $column, array $values) {
		parent::__construct();

		$this->property = $property;
		$this->vals = $values;
	}


	public function resolve(int $index = 0) : int {
		$placeholders = [];
		foreach($this->vals as $value){
			$placeholders[] = ":cond_{$index}";
			$this->values["cond_{$index}"] = $value;
			$index++;
		}

		$placeholder_str = implode(', ', $placeholders);
		$this->query = "{$this->property->get_db_table()}.{$this->property->get_db_column()} IN ({$placeholder_str})";

		return $index;
	}
}
?>
