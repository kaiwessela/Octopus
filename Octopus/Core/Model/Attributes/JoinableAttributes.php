<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;

trait JoinableAttributes {
	# requires:
	# protected string $class;


	final public function is_joinable() : bool {
		return true;
	}


	final public function get_class() : string {
		return $this->class;
	}


	final public function get_detection_column() : string {
		return "{$this->get_prototype()->get_prefixed_db_table()}.id";
	}


	abstract public function get_prototype() : Entity|Relationship;
}
?>
