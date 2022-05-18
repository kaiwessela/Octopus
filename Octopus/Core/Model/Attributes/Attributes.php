<?php
namespace Octopus\Core\Model\Attributes;

# This trait shares common methods for Entity and Relationship that relate to the loading, altering, validating and
# outputting of attributes.
# It heavily uses the AttributeDefinition class, so it may be wise to take a look on that too.

trait Attributes {
	# This trait requires the following to be defined in every class using it:
	# const DB_TABLE; – the name of the database table containing the objects data
	# const DB_PREFIX; – the prefix that is added to the objects columns on database requests (without underscore [_])
	# protected array $attributes; – the Attributes stored here
	# protected readonly […, depending] $context; – a reference to the context entity/list/relationship/…


	# Disable the database access for this entity and all other entities it contains.
	# this function should be called by all controllers handing over this entity to templates etc. in order to output it
	# this is a safety feature that prevents templates from altering or deleting entity data
	final public function freeze() : void {
		# if this entity is currently in the freezing process, do nothing. this prevents endless loops.
		if($this->flow->is_at('freezing')){
			return;
		}

		$this->flow->step('freezing'); # start the freezing process
		$this->db->disable();

		foreach($this->attributes as $name => $_){
			$this->attributes[$name]->freeze();
		}

		$this->flow->step('frozen'); # finish the freezing process
	}


	function __get($name) {
		# if $name is a defined attribute, return its value
		if(isset($this->attributes[$name])){
			return $this->attributes[$name]->get_value();
		}
	}


	function __isset(string $name) : bool {
		# if $name is a defined attribute, return whether it is set
		if(isset($this->attributes[$name])){
			return $this->attributes[$name]->is_empty();
		} else {
			return false;
		}
	}
}
?>
