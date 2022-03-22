<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\AttributeDefinition;

// TODO explainations

class InList extends Condition {
	protected AttributeDefinition $attribute;
	protected array $vals;


	function __construct(AttributeDefinition $column, array $values) {
		parent::__construct();

		$this->attribute = $column;
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
		$this->query = "{$this->attribute->get_db_table()}.{$this->attribute->get_db_column()} IN ({$placeholder_str})";

		return $index;
	}
}
?>
