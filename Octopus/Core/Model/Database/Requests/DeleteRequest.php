<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \Exception;

// TODO explainations

final class DeleteRequest extends Request {
	# inherited from Request:
	# protected Entity|Relationship $object;
	# protected array $attributes;
	# protected string $query;
	# protected array $values;

	protected IdentifierEquals $condition;


	# ---> Request:
	# function __construct(Entity|Relationship $object);
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function is_resolved() : bool;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function is_resolved() : bool;



	final protected function set_condition(IdentifierEquals $condition) : void {
		$this->condition = $condition;
	}


	final protected function resolve() : void {
		if(!isset($this->condition)){
			throw new Exception('An IdentifierEquals condition must be set for this request.');
		}

		$this->query = "DELETE FROM `{$this->object->get_db_table()}` WHERE {$this->condition->get_query()}";
		$this->values = $this->condition->get_values();
	}
}
?>
