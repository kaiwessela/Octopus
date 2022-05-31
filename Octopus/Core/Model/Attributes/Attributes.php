<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\Collection;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Attributes\Exceptions\IdentifierCollisionException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Attributes\Exceptions\EntityNotFoundException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use Exception;

# This trait shares common methods for Entity and Relationship that relate to the loading, altering, validating and
# outputting of attributes.
# It heavily uses the AttributeDefinition class, so it may be wise to take a look on that too.

trait Attributes {
	# This trait requires the following to be defined in every class using it:
	# const DB_TABLE; – the name of the database table containing the objects data
	# const DB_PREFIX; – the prefix that is added to the objects columns on database requests (without underscore [_])
	# const ATTRIBUTES; – an array of raw definitions of the objects attributes
	# protected static array $attributes; – the raw definitions are turned into AttributeDefinitions and stored here
	# protected readonly […, depending] $context; – a reference to the context entity/list/relationship/…


	final protected static function load_attribute_definitions() : void {
		static::$attributes = [];

		foreach(static::define_attributes() as $name => $attribute){
			$attribute->init($name, static::class);
			static::$attributes[$name] = $attribute;
		}
	}


	# Return the array of Attributes for this class (static::$attributes). If they are not loaded yet, load them
	final public static function get_attribute_definitions() : array {
		if(!isset(static::$attributes)){
			static::load_attribute_definitions();
		}

		return static::$attributes;
	}


	final protected function bind_attributes() : void {
		foreach(static::$attributes as $name => $attribute){
			$this->$name = clone $attribute;
			$this->$name->bind($this);
		}
	}


	# Change an attributes value (and check before whether that change is allowed)
	# @param $name: the name of the attribute
	# @param $input: the proposed new value
	# @throws: AttributeValueException[List] if changing the attribute to the proposed value is not allowed
	final public function edit_attribute(string $name, mixed $input) : void {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		if(!isset(static::$attributes[$name])){
			throw new Exception("Attribute «{$name}» is not defined.");
		}

		$attribute = static::$attributes[$name];

		if($attribute instanceof RelationshipAttribute){
			if(!$this->is_independent()){
				return;
			}

			$this->$name->edit($input);

	   } else {
			$this->$name->edit($input);
		}
	}


	// TODO from here
	function __get($name) {
		# if $this->$name is a defined attribute, return its value
		if(isset(static::get_attribute_definitions()[$name])){
			$definition = static::$attributes[$name];

			# if the attribute is contextual, let the context relationship return its value (if there is one)
			// if($definition->type_is('contextual')){
			// 	if($this->context instanceof Relationship){
			// 		return $this->context->$name;
			// 	} else {
			// 		return null;
			// 	}
			// } else {
				return $this->$name->get_value();
			// }
		}
	}


	function __isset(string $name) : bool {
		return array_key_exists($name, static::get_attribute_definitions());
	}
}
?>
