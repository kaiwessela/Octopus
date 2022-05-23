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
			if($attribute instanceof EntityAttribute || $attribute instanceof RelationshipAttribute){
				$this->$name = null;
			} else {
				$this->$name = clone $attribute;
				$this->$name->bind($this);
			}
		}
	}


	# Change an attributes value (and check before whether that change is allowed)
	# @param $name: the name of the attribute
	# @param $input: the proposed new value
	# @throws: AttributeValueException[List] if changing the attribute to the proposed value is not allowed
	final public function edit_attribute(string $name, mixed $input) : void {
		$this->flow->check_step('edited');

		if(!isset(static::$attributes[$name])){
			throw new Exception("Attribute «{$name}» is not defined.");
		}

		$attribute = static::$attributes[$name];

		if($attribute instanceof EntityAttribute){
			$class = $attribute->get_class();

			# an Entity can be received in various ways: as an already loaded instance of Entity,
			# as an id of an entity in the database or as an array with data to create a new entity from.

			if($input instanceof Entity){ # input is an already loaded instance of Entity
				# check whether the input object is of the correct class
				if($input::class !== $class){
					throw new IllegalValueException($attribute, $input, 'wrong class');
				}

				$object = $input;

			} else if(is_string($input) || (is_array($input) && !empty($input['id']))){ # input is an id
				# the input of an id can have two different forms: first, it can simply be a string with the id,
				# or second, it can be an array containing a key 'id' with the value being a string with the id.

				$id = $input['id'] ?? $input; # unify both input forms into a single variable
				$object = new $class();

				# check whether an entity of the prescribed class with this id exists
				try {
					$object->pull($id);
				} catch(EmptyResultException $e){
					# no entity with this id was found; throw an exception
					throw new EntityNotFoundException($attribute, $id);
				}

			// DEPRECATED - no more supported because it creates side effects
			// } else if(is_array($input)){ # input is an array that contains data to create a new Entity from
			// 	if(!$this->db->is_local() && !$definition->is_alterable()){
			// 		throw new AttributeNotAlterableException($definition, $this, $input);
			// 	}
			//
			// 	# construct a new entity of the prescribed class
			// 	$cls = $definition->get_class();
			// 	$object = new $cls($this); # set $this as the context entity
			// 	$object->create(); # initialize (create) the entity
			//
			// 	# try to fill the new entity with the received data
			// 	# MAY THROW an AttributeValueExceptionList
			// 	$object->receive_input($input);

			} else if(empty($input)){ # input is empty or null
				if($attribute->is_required()){ # if the attribute is required to be set, throw an error
					throw new MissingValueException($attribute);
				} else { # otherwise just set it to null
					$this->$name = null;
				}

			} else { # unsupported format, throw an exception
				throw new AttributeValueException($attribute, 'Unsupported input format.', $input);
			}

			# check whether the attribute value changed by comparing the current and the new entities’ ids
			# if there is no difference, don't change the attribute at all
			if($object?->id !== $this->$name?->id){
				if(!$attribute->is_alterable() && !$this->db->is_local()){ // TODO
					throw new AttributeNotAlterableException($attribute, $this, $object);
				}

				$this->$name = $object;
				$this->db->set_altered();
			}

		} else if($attribute instanceof RelationshipAttribute){
			// TODO maybe remove this and move it into the DataObject::receive_input() function as it only makes sense there
		   # relationship lists cannot be edited if this object is not independent
		   if(isset($this->context)){
			   return;
		   }

		   $this->$name->receive_input($input); # let the relationship list handle the input

	   } else if($attribute instanceof Attribute){
			$this->$name->edit($input);

			if($this->$name->has_been_edited()){
				$this->db->set_altered();
			}

		} else {
			throw new Exception("No Attribute »{$name}« in this Entity.");
		}




		// DEPRECATED begin
		# set a variable for the current value to check (later) whether the value actually has changed
		// $former_value = $this->$name;
		//
		// if($definition->type_is('contextual')){ # handle contextual attributes
		// 	if(!$this->context instanceof Relationship){ # if the context is not a relationship, do nothing
		// 		return;
		// 	}
		//
		// 	$this->context->edit_attribute($name, $input); # let the context relation handle the attribute
		//
		// } else if($definition->type_is('custom')){ # handle custom attributes
		// 	$this->edit_custom_attribute($definition, $input);
		//
		// } else if($definition->type_is('identifier')){ # handle attributes of type identifier
		// 	if($input === $former_value){ # if the value did not change, do nothing
		// 		return;
		// 	}
		//
		// 	if(empty($input)){ # if the input is empty but the attribute is required to be set, throw an error
		// 		if($definition->is_required()){
		// 			throw new MissingValueException($definition);
		// 		} else { # otherwise just set it to null
		// 			$this->$name = null;
		// 		}
		// 	}
		//
		// 	# the following steps are only relevant for non-id identifiers, as ids never pass beyond this point
		//
		// 	# check whether the attribute is tried to be altered despite not being alterable and not being local
		// 	# (check is_local also because on local objects, even not-alterable attributes must be settable)
		// 	if(!$definition->is_alterable() && !$this->db->is_local()){
		// 		throw new AttributeNotAlterableException($definition, $this, $input);
		// 	}
		//
		// 	# check whether the input matches the defined constraints given
		// 	$definition->validate_input($input); # throws an IllegalValueException if failing
		//
		// 	# check whether the input is already set as an identifier on another object
		// 	# this does not invoke on relationships because they can only have an id and no additional identifiers
		// 	try {
		// 		# to do that, try to pull an object of the same class using the input as identifier
		// 		$double = new $this;
		// 		$double->pull($input, identify_by:$name);
		// 		throw new IdentifierCollisionException($definition, $double); # worked -> identifier is already used
		// 	} catch(EmptyResultException $e){
		// 		# it didn't work -> identifier is not used on another object
		// 		$this->$name = $input; # set the new attribute value
		// 	}
		//
		// 	$this->db->set_altered();
		//
		// # from here on, $definition->type === object or entity
		// } else if($definition->class_is(Collection::class)){
		// 	// todo
		// 	throw new Exception('Collections are not yet supported.');
		//
		// } else if($definition->supclass_is(StaticObject::class)){
		// 	if($input instanceof AttributeDefinition){ # dry run; used by StaticObjects’ internal edit methods
		// 		return;
		// 	}
		//
		// 	if(is_null($this->$name)){ # if no StaticObject exists yet, create a new one
		// 		$cls = $definition->get_class();
		// 		$this->$name = new $cls($this, $definition);
		// 	}
		//
		// 	$this->$name->edit($input);
		//
		// }
		// deprecated end




		$this->flow->step('edited');
	}


	# Return an array of all attribute values to send them to the database. also check for missing values
	final protected function get_push_values() : array {
		$result = [];

		# create an exception list to buffer all occuring MissingValueExceptions
		$errors = new AttributeValueExceptionList();

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				if(is_null($this->$name) && $attribute->is_required()){
					$errors->push(new MissingValueException($attribute));
				} else {
					$result[$name] = $this->$name?->id;
				}
			} else if($attribute instanceof RelationshipAttribute){
				continue;
			} else {
				if($attribute->is_required() && $this->$name->is_empty()){
					$errors->push(new MissingValueException($attribute));
				} else if($this->$name->has_been_edited()){
					$result[$name] = $this->$name->get_push_value();
				}
			}
		}



		// DEPRECATED begin
		// foreach(static::$attributes as $name => $definition){
		// 	# check whether the attribute is empty but required
		// 	if(is_null($this->$name) && $definition->is_required()){
		// 		$errors->push(new MissingValueException($definition));
		// 	}
		//
		// 	if($definition->type_is('primitive') || $definition->type_is('identifier')){
		// 		if($definition->is_alterable() || $this->db->is_local()){
		// 			if($definition->class_is('bool')){ # convert booleans to int
		// 				$result[$name] = (int) $this->$name;
		// 			} else {
		// 				$result[$name] = $this->$name;
		// 			}
		// 		}
		// 	} else if($definition->type_is('custom')){
		// 		if(!$definition->is_alterable() && !$this->db->is_local()){
		// 			continue;
		// 		}
		//
		// 		try {
		// 			$result[$name] = $this->get_custom_push_value($definition);
		// 		} catch(AttributeValueException $e){
		// 			$errors->push($e);
		// 		} catch(AttributeValueExceptionList $e){
		// 			$errors->merge($e);
		// 		}
		// 	} else if($definition->class_is(Collection::class)){
		// 		// todo
		// 	} else if($definition->supclass_is(StaticObject::class)){
		// 		$result[$name] = $this->$name?->export();
		// 	} else if($definition->supclass_is(Entity::class)){
		// 		if($definition->is_alterable()){
		// 			$result[$name] = $this->$name?->id;
		// 		}
		// 	}
		// }
		// deprecated end




		if(!$errors->is_empty()){
			throw $errors;
		}

		return $result;
	}


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

		# freeze all attributes that are entities or relationships
		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute || $attribute instanceof RelationshipAttribute){
				$this->$name?->freeze();
			}
		}

		$this->flow->step('frozen'); # finish the freezing process
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
		# if $this->$name is a defined attribute, return whether it is set
		if(isset(static::get_attribute_definitions()[$name])){
			$definition = static::$attributes[$name];
			# if the attribute is contextual, let the context relationship return its value (if there is one)
			// if($definition->type_is('contextual') && $this->context instanceof Relationship){
			// 	return isset($this->context->$name);
			// } else {
				return $this->$name->is_empty();
			// }
		} else {
			return false;
		}
	}
}
?>
