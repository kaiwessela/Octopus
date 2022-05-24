<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;

abstract class PropertyAttribute extends Attribute {

	abstract public function load(null|string|int|float $data) : void;


	final public function get_db_column() : string {
		return $this->name;
	}
}
?>
