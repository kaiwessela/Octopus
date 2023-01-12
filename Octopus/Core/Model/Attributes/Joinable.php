<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Database\Requests\Join;

trait Joinable {
	# requires:
	# protected string $class;


	final public function is_joinable() : bool {
		return true;
	}


	final public function get_class() : string {
		return $this->class;
	}


	abstract public function get_join_request(array $include_attributes) : Join;


	abstract public function get_detection_column() : string;


	abstract public function get_prototype() : Entity|Relationship;
}
?>
