<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Exception;

class RelationshipAttribute extends Attribute {
	protected RelationshipList|Relationship $prototype;
	protected string $single_class;
	protected string $list_class;


	public static function define(string $class) : RelationshipAttribute {
		$attr = new RelationshipAttribute();

		if(!class_exists($class) || !is_subclass_of($class, RelationshipList::class)){
			throw new Exception("Invalid class «{$class}».");
		} else {
			$attr->list_class = $class;
			$attr->single_class = $class::RELATION_CLASS; // TODO validate
		}

		$attr->required = false;
		$attr->editable = true;
		$attr->class = $class;
		return $attr;
	}


	final public function bind(string $name, Entity|Relationship $parent) : void {
		parent::bind($name, $parent);

		$class = $this->get_class();
		$this->prototype = new $class($this->parent, null, $this->get_name());
	}


	final public function load(array $data, ?DatabaseAccess $db = null) : void {
		if($this->parent->is_independent()){
			$class = $this->get_list_class();
		} else {
			$class = $this->get_single_class();
		}

		$this->value = new $class($this->parent, $db);
		$this->value->load($data);
	}


	final public function edit(mixed $value) : void {
		$this->value->receive_input($value);
	}


	final public function get_db_column() : string { // so that Entity->load() can find out whether this relationlist has been pulled
		return 'id';
	}


	public function get_single_class() : string {
		return $this->single_class;
	}


	public function get_list_class() : string {
		return $this->list_class;
	}


	public function get_push_value() : null|string|int|float {
		throw new Exception('do not call!');
	}


	public function get_join() : JoinRequest {
		$class = $this->get_single_class();
		$prototype = new $class($this->parent);
		$prototype->join();
	}
}
?>
