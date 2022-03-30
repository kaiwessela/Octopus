<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;

abstract class StaticObject {
	protected Entity $context;
	protected AttributeDefinition $definition;

	// TODO check


	function __construct(Entity &$context, AttributeDefinition $definition) {
		$this->context = $context;
		$this->definition = $definition;
	}


	final protected function check_edit() : void {
		$this->context->edit_attribute($this->definition->get_name(), $this->definition); # pass definition to signal a dry run
	}


	abstract public function load(mixed $data) : void;
	abstract public function edit(mixed $data) : void;
	abstract public function export() : mixed;
	abstract public function arrayify() : mixed;
}
?>
