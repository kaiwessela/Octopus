<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use Exception;

class JoinRequest extends Request {
	protected PropertyDefinition $native_property;
	protected PropertyDefinition $foreign_property;

	use SelectAndJoin;

	# for SelectAndJoin
	protected array $columns;
	protected array $joins;


	function __construct(string $table, PropertyDefinition $native_property, PropertyDefinition $foreign_property) {
		parent::__construct($table);

		$this->joins = [];
		$this->columns = [];

		if($native_property->get_db_table() !== $this->table){
			throw new Exception('Native Property must be part of the joined table.');
		}

		if(!$native_property->type_is('identifier') && !$native_property->supclass_is(DataObject::class)){
			throw new Exception('Native Property must be an identifier or an id of a foreign object.');
		}

		if($foreign_property->get_db_table() === $this->table){
			throw new Exception('Foreign Property must not be part of the joined table.');
		}

		if(!$foreign_property->type_is('identifier') && !$foreign_property->supclass_is(DataObject::class)){
			throw new Exception('Foreign Property must be an identifier or an id of a foreign object.');
		}

		$this->native_property = $native_property;
		$this->foreign_property = $foreign_property;
	}


	protected function resolve() : void {
		$this->cycle->step('resolve');
		
		foreach($this->properties as $property){
			$this->columns[] = static::create_column_string($property);
		}

		$native_col = "{$this->native_property->get_db_table()}.{$this->native_property->get_db_column()}";
		$foreign_col = "{$this->foreign_property->get_db_table()}.{$this->foreign_property->get_db_column()}";

		$this->query = "LEFT JOIN {$this->table} ON {$native_col} = {$foreign_col}".PHP_EOL;

		foreach($this->joins as $join){
			$this->query .= $join->get_query();
			$this->columns = array_merge($this->columns, $join->get_columns());
		}
	}


	public function get_foreign_property() : PropertyDefinition {
		return $this->foreign_property;
	}





	protected function validate_condition(?Condition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
