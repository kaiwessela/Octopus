<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use Exception;

// TODO explainations

class IdentifierEquals extends Condition {
	protected AttributeDefinition $attribute;
	protected string $value;


	function __construct(AttributeDefinition $attribute, string $value) {
		parent::__construct();

		if(!$attribute->type_is('identifier')){
			throw new Exception("Property must be of type identifier. «{$attribute->get_type()}» given.");
		}

		$this->attribute = $attribute;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_db_table()}.{$this->attribute->get_db_column()} = :cond_{$index}";
		$this->values = ["cond_{$index}" => $this->value];

		return $index + 1;
	}
}
?>
