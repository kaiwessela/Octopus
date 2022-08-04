<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Attribute;

abstract class StaticObject {
	protected Entity $context;
	protected Attribute $attribute;

	// TODO check


	function __construct(Entity &$context, Attribute $attribute) {
		$this->context = $context;
		$this->attribute = $attribute;
	}


	abstract public function load(mixed $data) : void;
	abstract public function edit(mixed $data) : void;
	abstract public function export() : mixed;
	abstract public function arrayify() : mixed;

	// abstract public function equals(StaticObject $object) : bool;
}
?>
