<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEqualsCondition;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use \Exception;

// TODO explainations

final class UpdateRequest extends Request {
	# inherited from Request:
	# protected string $table;
	# protected array $attributes;
	# protected string $query;
	# protected array $values;

	protected IdentifierCondition $condition;


	# ---> Request:
	# function __construct(string $table);
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function is_resolved() : bool;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function is_resolved() : bool;



	final protected function set_condition(IdentifierEqualsCondition $condition) : void {
		$this->condition = $condition;
	}


	final public function set_values(array $values) : void {
		$this->values = $values + $this->values; # values with the same key are overwritten, all others just stay
	}


	final public function resolve() : void {
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		if(!isset($this->condition)){
			throw new Exception('An IdentifierCondition must be set for this request.');
		}

		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	{$attribute->get_db_column()} = :{$attribute->get_name()}";
		}

		$this->query = "UPDATE `{$this->table}` SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns).PHP_EOL;
		$this->query .= "WHERE {$this->condition->get_query()}".PHP_EOL;

		$this->set_values($this->condition->get_values());
	}
}
