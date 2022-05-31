<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use Exception;

// TODO explainations

class JoinRequest extends Request {
	protected ?string $table_alias;
	protected Attribute $native_attribute;
	protected Attribute $foreign_attribute;

	use SelectAndJoin;

	# for SelectAndJoin
	protected array $columns;
	protected array $joins;


	function __construct(string $table, ?string $table_alias, Attribute $native_attribute, Attribute $foreign_attribute) {
		parent::__construct($table);

		$this->table_alias = $table_alias;

		$this->joins = [];
		$this->columns = [];

		if($native_attribute->get_db_table() !== $this->table){
			throw new Exception('Native Attribute must be part of the joined table.');
		}

		if(!$native_attribute instanceof IdentifierAttribute && !$native_attribute instanceof EntityAttribute){
			throw new Exception('Native Attribute must be an identifier or an id of a foreign object.');
		}

		if($foreign_attribute->get_db_table() === $this->table){
			throw new Exception('Foreign Attribute must not be part of the joined table.');
		}

		if(!$foreign_attribute instanceof IdentifierAttribute && !$foreign_attribute instanceof EntityAttribute){
			throw new Exception('Foreign Attribute must be an identifier or an id of a foreign object.');
		}

		$this->native_attribute = $native_attribute;
		$this->foreign_attribute = $foreign_attribute;
	}


	protected function resolve() : void {
		$this->flow->step('resolve');

		foreach($this->attributes as $attribute){
			$this->columns[] = static::create_column_string($attribute, $this->table_alias);
		}

		$native_col = $this->native_attribute->get_a_full_db_column($this->table_alias);
		$foreign_col = $this->foreign_attribute->get_full_db_column();

		if(!is_null($this->table_alias)){
			$this->query = "LEFT JOIN `{$this->table}` AS `{$this->table_alias}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		} else {
			$this->query = "LEFT JOIN `{$this->table}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		}

		foreach($this->joins as $join){
			$this->query .= $join->get_query();
			$this->columns = array_merge($this->columns, $join->get_columns());
		}
	}


	public function get_foreign_attribute() : Attribute {
		return $this->foreign_attribute;
	}





	protected function validate_condition(?Condition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
