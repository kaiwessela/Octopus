<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Exception;

class RelationshipAttribute extends Attribute {
	protected string $class;


	public static function define(string $class) : RelationshipAttribute {
		if(!class_exists($class) || !is_subclass_of($class, RelationshipList::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		$attr = new RelationshipAttribute();
		$attr->required = false;
		$attr->editable = true;
		$attr->class = $class;
		return $attr;
	}


	final public function load(array $data, DatabaseAccess $db, bool $complete, ?Entity &$shared_relatum = null) : void {
		$class = $this->get_class();
		$this->value = new $class($this->parent, $db);
		$this->value->load($data, $complete, $shared_relatum);
	}


	final public function edit(mixed $value) : void {
		$this->value->receive_input($value);
	}


	final public function get_db_column() : string {
		return $this->class::RELATION_CLASS::get_attribute_definitions()['id']->get_db_column();
	}


	public function get_class() : string {
		return $this->class;
	}


	public function get_push_value() : null|string|int|float {
		throw new Exception('do not call!');
	}


	// public function get_join() : JoinRequest {
	// 	$class = $this->get_class();
	// 	$prototype = new $class($this->parent);
	// 	$prototype->join()
	// }
}
?>
