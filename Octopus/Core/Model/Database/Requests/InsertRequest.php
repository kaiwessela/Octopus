<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;

// TODO explainations

final class InsertRequest extends Request {
	# inherited from Request:
	# protected string $table;
	# protected array $attributes;
	# protected string $query;
	# protected array $values;


	# ---> Request:
	# function __construct(string $table);
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function is_resolved() : bool;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function is_resolved() : bool;



	final public function set_values(array $values) : void {
		$this->values = $values + $this->values; # values with the same key are overwritten, all others just stay
	}


	final protected function resolve() : void {
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	`{$attribute->get_db_column()}` = :{$attribute->get_name()}";
		}

		$this->query = "INSERT INTO `{$this->table}` SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns);
	}
}
?>
