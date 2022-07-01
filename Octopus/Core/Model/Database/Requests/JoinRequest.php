<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Exception;

// TODO explainations

final class JoinRequest extends Request {
	# inherited from Request:
	# protected string $table;
	# protected array $attributes;
	# protected string $query;
	# protected ?array $values;

	protected string $table_alias;

	protected Attribute $native_attribute;
	protected Attribute $foreign_attribute;

	use SelectAndJoin;

	# required by SelectAndJoin
	protected array $columns;
	protected array $joins;


	# ---> Request:
	# function __construct();
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function set_values(array $values) : void;
	# final public function is_resolved() : bool;


	function __construct(string $table, string $table_alias, Attribute $native_attribute, Attribute $foreign_attribute) {
		parent::__construct($table);

		$this->table_alias = $table_alias;

		$this->joins = [];
		$this->columns = [];

		if($native_attribute->get_prefixed_db_table() !== $this->table_alias){
			throw new Exception('Native Attribute must be part of the joined table.');
		}

		if(!$native_attribute instanceof IdentifierAttribute && !$native_attribute instanceof EntityAttribute){
			throw new Exception('Native Attribute must be an identifier or an id of a foreign object.');
		}

		if($foreign_attribute->get_prefixed_db_table() === $this->table_alias){
			throw new Exception('Foreign Attribute must not be part of the joined table.');
		}

		if(!$foreign_attribute instanceof IdentifierAttribute && !$foreign_attribute instanceof EntityAttribute){
			throw new Exception('Foreign Attribute must be an identifier or an id of a foreign object.');
		}

		$this->native_attribute = $native_attribute;
		$this->foreign_attribute = $foreign_attribute;
	}


	public function resolve() : void {
		foreach($this->attributes as $attribute){
			$this->columns[] = static::create_column_string($attribute);
		}

		$native_col = $this->native_attribute->get_prefixed_db_column();
		$foreign_col = $this->foreign_attribute->get_prefixed_db_column();

		if($this->table !== $this->table_alias){
			$this->query = "LEFT JOIN `{$this->table}` AS `{$this->table_alias}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		} else {
			$this->query = "LEFT JOIN `{$this->table}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		}

		foreach($this->joins as $join){
			$this->query .= $join->get_query();
			$this->columns = array_merge($this->columns, $join->get_columns());
		}
	}


	public function get_columns() : array {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->columns;
	}


	public function get_foreign_attribute() : Attribute {
		return $this->foreign_attribute;
	}


	public function is_multijoin() : bool {
		return !($this->native_attribute instanceof IdentifierAttribute);
	}
}
?>
