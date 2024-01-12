<?php
namespace Octopus\Core\Model\Attributes;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\Joinable;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Requests\Join;
use Octopus\Core\Model\Relationship;
use Octopus\Core\Model\RelationshipList;

final class RelationshipsReference extends Attribute {
	# inherited from Attribute
	# protected Entity|Relationship $parent;
	# protected string $name;
	# protected bool $is_loaded;
	# protected bool $is_required;
	# protected bool $is_editable;
	# protected bool $is_dirty;
	# protected mixed $value;

	protected string $class;
	protected Relationship $prototype;
	protected RelationshipList $list_prototype;


	# ---> Attribute
	# final public function bind(string $name, Entity|Relationship $parent) : void;
	# final public function is_loaded() : bool;
	# final public function is_required() : bool;
	# final public function is_editable() : bool;
	# final public function is_dirty() : bool;
	# final public function set_clean() : void;
	# public function is_pullable() : bool;
	# final public function get_name() : string;
	# final public function get_db_table() : string;
	# final public function get_prefixed_db_table() : string;
	# final public function &get_value() : mixed;
	# public function is_empty() : bool;

	use Joinable;
	# final public function is_joinable() : bool;
	# final public function get_class() : string;
	# final public function get_detection_column() : string;



	final public static function define(string $class) : RelationshipsReference {
		if(!class_exists($class) || !is_subclass_of($class, Relationship::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		$attribute = new static(false, true);
		$attribute->class = $class;

		return $attribute;
	}


	final public function load(?array $data, bool $is_complete = false) : void {
		$this->value = clone $this->get_list_prototype();

		if(is_null($data) || is_null($data[0][$this->get_detection_column()])){
			$this->value->load([], $is_complete);
		} else {
			$this->value->load($data, $is_complete);
		}

		$this->is_loaded = true;
	}


	final protected function _edit(mixed $value) : void {
		$this->value->receive_input($value);
	}


	final public function get_list_class() : string {
		return $this->list_class;
	}


	final public function get_prototype() : Relationship {
		if(!isset($this->prototype)){
			$class = $this->get_class();
			$this->prototype = new $class();
			$this->prototype->contextualize(entity:$this->parent, attribute:$this);
		}

		return $this->prototype;
	}


	final public function get_list_prototype() : RelationshipList {
		if(!isset($this->list_prototype)){
			$class = $this->get_class();
			$this->list_prototype = $class::list();
			$this->list_prototype->contextualize(entity:$this->parent, attribute:$this);
		}

		return $this->list_prototype;
	}


	final public function get_detection_column() : string {
		return "{$this->get_prototype()->get_prefixed_db_table()}.id";
	}


	final public function get_join_request(array $include_attributes) : Join {
		return $this->get_prototype()->join_reverse($include_attributes);
	}


	final public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_array($option)){
			return $this->get_prototype()->resolve_pull_conditions($option);
		} else {
			throw new Exception('invalid option.'); // TODO
		}
	}


	final public function arrayify() : null|string|int|float|bool|array {
		return $this->value?->arrayify();
	}
}
?>