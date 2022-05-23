<?php
namespace Octopus\Core\Model\Attributes;
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


	final protected function check_edit() : void { // TODO
		$this->context->edit_attribute($this->attribute->get_name(), $this->attribute); # pass definition to signal a dry run
	}


	abstract public function load(mixed $data) : void;
	abstract public function edit(mixed $data) : void;
	abstract public function export() : mixed;
	abstract public function arrayify() : mixed;
}
?>
