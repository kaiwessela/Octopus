<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use Exception;

class IdentifierCondition extends Condition {
	protected PropertyDefinition $property;
	protected string $value;


	function __construct(PropertyDefinition $property, string $value) {
		parent::__construct();

		if(!$property->type_is('identifier')){
			throw new Exception("Property must be of type identifier. «{$property->get_type()}» given.");
		}

		$this->property = $property;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->property->get_db_table()}.{$this->property->get_db_column()} = :cond_{$index}";
		$this->values = ["cond_{$index}" => $this->value];

		return $index + 1;
	}
}
?>
