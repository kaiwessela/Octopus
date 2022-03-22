<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use Exception;

// TODO explainations

class JoinRequest extends Request {
	protected AttributeDefinition $native_attribute;
	protected AttributeDefinition $foreign_attribute;

	use SelectAndJoin;

	# for SelectAndJoin
	protected array $columns;
	protected array $joins;


	function __construct(string $table, AttributeDefinition $native_attribute, AttributeDefinition $foreign_attribute) {
		parent::__construct($table);

		$this->joins = [];
		$this->columns = [];

		if($native_attribute->get_db_table() !== $this->table){
			throw new Exception('Native Attribute must be part of the joined table.');
		}

		if(!$native_attribute->type_is('identifier') && !$native_attribute->supclass_is(Entity::class)){
			throw new Exception('Native Attribute must be an identifier or an id of a foreign object.');
		}

		if($foreign_attribute->get_db_table() === $this->table){
			throw new Exception('Foreign Attribute must not be part of the joined table.');
		}

		if(!$foreign_attribute->type_is('identifier') && !$foreign_attribute->supclass_is(Entity::class)){
			throw new Exception('Foreign Attribute must be an identifier or an id of a foreign object.');
		}

		$this->native_attribute = $native_attribute;
		$this->foreign_attribute = $foreign_attribute;
	}


	protected function resolve() : void {
		$this->flow->step('resolve');

		foreach($this->attributes as $attribute){
			$this->columns[] = static::create_column_string($attribute);
		}

		$native_col = "{$this->native_attribute->get_db_table()}.{$this->native_attribute->get_db_column()}"; // TODO use shortcut
		$foreign_col = "{$this->foreign_attribute->get_db_table()}.{$this->foreign_attribute->get_db_column()}";

		$this->query = "LEFT JOIN {$this->table} ON {$native_col} = {$foreign_col}".PHP_EOL;

		foreach($this->joins as $join){
			$this->query .= $join->get_query();
			$this->columns = array_merge($this->columns, $join->get_columns());
		}
	}


	public function get_foreign_attribute() : AttributeDefinition {
		return $this->foreign_attribute;
	}





	protected function validate_condition(?Condition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
