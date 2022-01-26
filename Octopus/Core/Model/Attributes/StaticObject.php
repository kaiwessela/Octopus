<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;

abstract class StaticObject {
	protected Entity $context;
	protected AttributeDefinition $definition;


	function __construct(Entity &$context, AttributeDefinition $definition, mixed $data) {
		$this->context = $context;
		$this->definition = $definition;

		if(!is_null($data)){
			$this->init($data);
		}
	}


	final protected function check_edit() : void {
		$this->context->edit_attribute($definition->get_name(), $definition); # pass definition to signal a dry run
	}


	abstract protected function init(mixed $data) : void;
	abstract public function edit(mixed $value) : void;
	abstract public function export() : mixed;
	abstract public function arrayify() : mixed;
}
?>
