<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;

// TODO explainations

class InList extends Condition {
	protected Attribute $attribute;
	protected array $vals;


	function __construct(Attribute $column, array $values) {
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
