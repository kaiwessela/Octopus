<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Exception;

// TODO explainations

final class JoinRequest extends Request {
	# inherited from Request:
	# protected Entity|Relationship $object;
	# protected array $attributes;
	# protected string $query;
	# protected ?array $values;

	protected Attribute $native_attribute;
	protected Attribute $foreign_attribute;

	protected string $direction; # forward|reverse

	use SelectAndJoin;

	# required by SelectAndJoin
	protected array $columns;
	protected array $joins;


	# ---> Request:
	# function __construct(Entity|Relationship $object);
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function set_values(array $values) : void;
	# final public function is_resolved() : bool;


	function __construct(Entity|Relationship $object, Attribute $native_attribute, Attribute $foreign_attribute) {
		parent::__construct($object);

		$this->joins = [];
		$this->columns = [];

		if($native_attribute instanceof IdentifierAttribute && $foreign_attribute instanceof EntityAttribute){
			$this->direction = 'forward';
		} else if($native_attribute instanceof EntityAttribute && $foreign_attribute instanceof IdentifierAttribute){
			$this->direction = 'reverse';
		} else {
			throw new Exception(); // TODO
		}

		if($native_attribute->get_prefixed_db_table() !== $this->object->get_prefixed_db_table()){
			throw new Exception('Native Attribute must be part of the joined table.');
		}

		if($foreign_attribute->get_prefixed_db_table() === $this->object->get_prefixed_db_table()){
			throw new Exception('Foreign Attribute must not be part of the joined table.');
		}

		$this->native_attribute = $native_attribute;
		$this->foreign_attribute = $foreign_attribute;
	}


	final protected function resolve() : void {
		foreach($this->attributes as $attribute){
			$this->columns[] = static::create_column_string($attribute);
		}

		$native_col = $this->native_attribute->get_prefixed_db_column();
		$foreign_col = $this->foreign_attribute->get_prefixed_db_column();

		if($this->object->get_db_table() !== $this->object->get_prefixed_db_table()){
			$this->query = "LEFT JOIN `{$this->object->get_db_table()}` AS `{$this->object->get_prefixed_db_table()}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		} else {
			$this->query = "LEFT JOIN `{$this->object->get_db_table()}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		}

		foreach($this->joins as $join){
			$this->query .= $join->get_query();
			$this->columns = array_merge($this->columns, $join->get_columns());
		}
	}


	final public function get_columns() : array {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->columns;
	}


	final public function is_forward_join() : bool {
		return $this->direction === 'forward';
	}


	final public function is_reverse_join() : bool {
		return $this->direction === 'reverse';
	}


	final public function is_convoluted() : bool {
		if($this->is_reverse_join()){
			return true;
		}

		foreach($this->joins as $join){
			if($join->is_convoluted()){
				return true;
			}
		}

		return false;
	}
}
?>
