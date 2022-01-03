<?php
namespace Octopus\Core\Model\Properties;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelation;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataObjectCollection;
use \Octopus\Core\Model\DataType;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueExceptionList;
use \Octopus\Core\Model\Properties\Exceptions\IdentifierCollisionException;
use \Octopus\Core\Model\Properties\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Properties\Exceptions\MissingValueException;
use \Octopus\Core\Model\Properties\Exceptions\PropertyNotAlterableException;
use \Octopus\Core\Model\Properties\Exceptions\RelationObjectNotFoundException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use Exception;

# This trait takes care of all standard operations for objects that are stored in the database and have properties
# themselves, which currently are DataObjects and DataObjectRelations.
# It heavily uses the PropertyDefinition class, so it may be wise to take a look on that too.

trait Properties {
	# This trait requires the following to be defined in every class using it:
	# const DB_PREFIX; – the prefix that is added to the object's columns on database requests (without underscore [_])
	# const PROPERTIES; – an array of raw definitions of the properties' objects
	# protected static array $properties; – the raw definitions are turned into PropertyDefinitions and stored here
	# protected readonly […, depending] $context; – a reference to the context object/list/relation/…


	# Generate and return a random value that is set as the object's id upon creation
	# The id is 8 characters long and consists of these characters: 0123456789abcdef (hexadecimal/base16)
	final protected static function generate_id() : string {
		return bin2hex(random_bytes(4)); # first generate 4 random bytes, then turn them into a hexadecimal string
	}


	# Turn the raw property definitions into PropertyDefinitions and store them in self::$properties
	final protected static function load_property_definitions() : void {
		static::$properties = [];

		foreach(static::PROPERTIES as $name => $raw_definition){
			static::$properties[$name] = new PropertyDefinition($name, $raw_definition, static::DB_TABLE, static::DB_PREFIX);
		}
	}


	# Set all properties to null, except the id
	final protected function initialize_properties() : void {
		foreach(static::$properties as $name => $definition){
			if($definition->class_is('id')){
				continue;
			}

			$this->$name = null;
		}
	}


	# Change a property's value (and check before whether that change is allowed)
	# @param $name: the name of the property
	# @param $input: the proposed new value
	# @throws: PropertyValueException[List] if changing the property to the proposed value is not allowed
	final public function edit_property(string $name, mixed $input) : void {
		$this->cycle->check_step('edited');

		if(empty($definition = self::$properties[$name])){
			throw new Exception("No PropertyDefinition found for property »{$name}«.");
		}

		# set a variable for the current value to check (later) whether the value actually has changed
		$former_value = $this->$name;

		if($definition->type_is('contextual')){ # handle contextual properties
			if(!$this->context instanceof DataObjectRelation){ # if the context is not a relation, do nothing
				return; // IDEA maybe throw an Exception
			}

			$this->context->edit_property($name, $input); # let the context relation handle the property

		} else if($definition->type_is('custom')){ # handle custom properties
			$this->edit_custom_property($name, $input);

		} else if($definition->type_is('identifier')){ # handle properties of type identifier
			if($input === $former_value){ # if the value did not change, do nothing
				return;
			}

			if(empty($input)){ # if the input is empty but the property is required to be set, throw an error
				if($definition->is_required()){
					throw new MissingValueException($definition);
				} else { # otherwise just set it to null
					$this->$name = null;
				}
			}

			# the following steps are only relevant for non-id identifiers, as ids never pass beyond this point

			# check whether the property is tried to be altered despite not being alterable and not being local
			# (check is_local also because on local objects, even not-alterable properties must be settable)
			if(!$definition->is_alterable() && !$this->db->is_local()){
				throw new PropertyNotAlterableException($definition, $this, $input);
			}

			# check whether the input matches the defined constraints given
			$definition->validate_input($input); # throws an IllegalValueException if failing

			# check whether the input is already set as an identifier on another object
			# this does not invoke on Relations because they can only have an id and no additional identifiers
			try {
				# to do that, try to pull an object of the same class using the input as identifier
				$double = new $this;
				$double->pull($input, identify_by:$name);
				throw new IdentifierCollisionException($definition, $double); # worked -> identifier is already used
			} catch(EmptyResultException $e){
				# it didn't work -> identifier is not used on another object
				$this->$name = $input; # set the new property value
			}

			$this->db->set_altered();

		} else if($definition->type_is('primitive')){ # handle properties of type primitive
			if(empty($input)){ # if the input is empty but the property is required to be set, throw an error
				if($definition->is_required()){
					throw new MissingValueException($definition);
				} else { # otherwise just set it to null
					$this->$name = null;
				}
			}

			# if input is a string, escape html characters first
			$escaped_input = is_string($input) ? htmlspecialchars($input) : $input;

			# check whether the property value has been altered
			# set the property value to the input
			if($escaped_input !== $former_value){
				if(!$definition->is_alterable() && !$this->db->is_local()){
					throw new PropertyNotAlterableException($definition, $this, $escaped_input);
				}

				# check whether the input matches the defined constraints
				$definition->validate_input($input); # throws an IllegalValueException if failing

				$this->$name = $escaped_input;
				$this->db->set_altered();
			}

		} else if($definition->type_is('object')){ # handle properties of type object

			// ----------------------------------------------------- TODO rewrite from here
			if($definition->supclass_is(DataType::class)){
				$class = $def->class;

				try {
					$this->$name = $class::import($input);
				} catch(InputException $e){ // TODO this is outdated
					$e->field = $name;
					throw $e;
				}

				// TODO altered
			// ----------------------------------------------------- end rewrite

			} else if($definition->supclass_is(DataObject::class)){
				# a DataObject can be received in various ways: as an already loaded DataObject object,
				# as an id of a DataObject in the database or as an array with data to create a new DataObject from.

				if($input instanceof DataObject){ # input is an already loaded DataObject object
					# check whether the input object is of the correct class
					if(!$definition->class_is($input::class)){
						throw new IllegalValueException($definition, $input, 'wrong class');
					}

					$object = $input;

				} else if(is_string($input) || (is_array($input) && !empty($input['id']))){ # input is an id
					# the input of an id can have two different forms: first, it can simply be a string with the id,
					# or second, it can be an array containing a key 'id' with the value being a string with the id.

					$id = $input['id'] ?? $input; # unify both input forms into a single variable
					$cls = $definition->get_class();
					$object = new $cls();

					# check whether an object of the prescribed class with this id exists
					try {
						$object->pull($id);
					} catch(EmptyResultException $e){
						# no object with this id was found; throw an exception
						throw new RelationObjectNotFoundException($definition, $id);
					}

				} else if(is_array($input)){ # input is an array that contains data to create a new DataObject from
					if(!$this->db->is_local() && !$definition->is_alterable()){
						throw new PropertyNotAlterableException($definition, $this, $input);
					}

					# construct a new object of the prescribed class
					$cls = $definition->get_class();
					$object = new $cls($this);
					//$object = new {$definition->get_class()}(&$this); # set $this as the context object
					$object->create(); # initialize (create) the object

					# try to fill the new object with the received data
					# MAY THROW a PropertyValueExceptionList
					$object->receive_input($input);

				} else if(empty($input)){ # input is empty or null
					if($definition->is_required()){ # if the property is required to be set, throw an error
						throw new MissingValueException($definition);
					} else { # otherwise just set it to null
						$this->$name = null;
					}

				} else { # unsupported format, throw an exception
					throw new PropertyValueException($definition, 'Unsupported input format.', $input);
				}

				# check whether the property value changed by comparing the current and the new objects' ids
				# if there is no difference, don't change the property at all
				if($object?->id !== $former_value?->id){
					if(!$definition->is_alterable() && !$this->db->is_local()){
						throw new PropertyNotAlterableException($definition, $this, $object);
					}

					$this->$name = $object;
					$this->db->set_altered();
				}

			} else if($definition->supclass_is(DataObjectRelationList::class)){ // IDEA maybe remove this and move it into the DataObject::receive_input() function as it only makes sense there
				# relationlists cannot be edited if this object is not independent
				if(isset($this->context)){
					return;
				}

				$this->$name->receive_input($input); # let the relationlist handle the input

			} else if($definition->supclass_is(DataObjectCollection::class)){
				throw new Exception('Collections are not yet supported.'); // TEMP
			}
		}

		$this->cycle->step('edited');
	}


	protected function edit_custom_property(PropertyDefinition $definition, mixed $input) : void {}


	# Return an array of all property values to send them to the database. also check for missing values
	final protected function get_push_values() : array {
		$result = [];

		# create an exception list to buffer all occuring MissingValueExceptions
		$errors = new PropertyValueExceptionList();

		foreach(self::$properties as $name => $definition){
			# check whether the property is empty but required
			if(is_null($this->$name) && $definition->is_required()){
				$errors->push(new MissingValueException($definition));
			}

			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				if($definition->is_alterable() || $this->db->is_local()){
					$result[$name] = $this->$name;
				}
			} else if($definition->type_is('object')){
				if($definition->supclass_is(DataObject::class)){
					if($definition->is_alterable()){
						$result["{$name}_id"] = $this->$name?->id;
					}
				} else if($definition->supclass_is(DataType::class)){
					// TODO
				}
			}
		}

		return array_merge($result, $this->get_custom_push_values());
	}


	# return format: [column name => value, …]
	protected function get_custom_push_values() : array {
		return [];
	}


	# Disable the database access for this object and all other objects it contains.
	# this function should be called by all controllers handing over this object to templates etc. in order to output it
	# this is a safety feature that prevents templates from altering or deleting object data
	final public function freeze() : void {
		# if this object is currently in the freezing process, do nothing. this prevents endless loops.
		if($this->cycle->is_at('freezing')){
			return;
		}

		$this->cycle->step('freezing'); # start the freezing process
		$this->db->disable();

		foreach(static::$properties as $name => $definition){
			# freeze all properties that are objects, except DataTypes
			if($definition->type_is('object') && !$definition->supclass_is(DataType::class)){
				$this->$name?->freeze();
			}
		}

		$this->cycle->step('frozen'); # finish the freezing process
	}


	# Return the PropertyDefinitions for this class (self::$properties). If they are not loaded yet, load them
	final public static function get_property_definitions() : array {
		if(!isset(static::$properties)){
			static::load_property_definitions();
		}

		return static::$properties;
	}


	function __get($name) : mixed {
		# if $this->$name is a defined property, return its value
		if(isset(static::$properties[$name])){
			$definition = static::$properties[$name];

			# if the property is contextual, let the context relation return its value (if there is one)
			if($definition->type_is('contextual')){
				if($this->context instanceof DataObjectRelation){
					return $this->context->$name;
				} else {
					return null;
				}
			} else {
				return $this->$name; // IDEA maybe reference for objects
			}
		}
	}


	function __isset(string $name) : bool {
		# if $this->$name is a defined property, return whether it is set
		if(isset(static::$properties[$name])){
			# if the property is contextual, let the context relation return its value (if there is one)
			if($definition->type_is('contextual') && $this->context instanceof DataObjectRelation){
				return isset($this->context->$name);
			} else {
				return isset($this->$name);
			}
		}
	}
}
?>
