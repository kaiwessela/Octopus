<?php // CODE ??, COMMENTS --, IMPORTS --
namespace Blog\Core\Model\Properties;

# This trait takes care about all standard operations for properties of DataObjects and friends,
# which means: Definition, Validation, Transformation
# It heavily uses the PropertyDefinition class, so it may be wise to take a look on that too.

trait Properties {
	# This trait requires the following constants in every class using it:
	# const DB_PREFIX;
	# const PROPERTIES;


	# Generate and return a random value that is set as the object's id upon creation
	# The id is 8 characters long and consists of these characters: 0123456789abcdef (hexadecimal/base16)
	final protected static function generate_id() : string {
		return bin2hex(random_bytes(4)); # first generate 4 random bytes, then turn them into a hexadecimal string
	}


	# This function loads property values received from a database request into this object.
	# @param $data: The fetched response from a successfully executed PDOStatement, containing the object data.
	# @param $relation_base_object: // TODO documentation
	# @param $relations: Whether RelationLists should be loaded (true) or not (false).
	final protected function load_properties(array $data, ?DataObject $relation_base_object = null, bool $relations = true) : void {
		# $data can have two formats, depending on whether relations were pulled or not:
		# Without Relations: (simple key-value array)
		# 	[
		#		'id' => 'abcdef01',
		#		'longid' => 'example-object',
		#		…
		# 	]
		# With Relations: (nested array)
		# 	[
		#		[
		#			'id' => 'abcdef01',
		#			'longid' => 'example-object',
		#			'relationobject_id' => '12345678',
		#			'relationclass_longid' => 'related-object-1',
		#			…
		#		],
		#		[
		#			'id' => 'abcdef01',
		#			'longid' => 'example-object',
		#			'relationobject_id' => 'abababab',
		#			'relationclass_longid' => 'related-object-2',
		#			…
		#		],
		#		…
		# 	]

		# The first example, a response without relations, has only one row (because only one object was pulled).
		# If relations are pulled using a JOIN statement, the columns of the related object are simply appended to the
		# row containing the base object's columns. If multiple related objects are pulled, for every row, the base
		# object's columns just get repeated (they are basically "filled up" with the same values).
		# To get the columns containing our object data, we must do a distinction:
		if(is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relations
		} else {
			$row = $data; # without relations
		}

		# loop through all property definitions and try to load the properties
		foreach($this->properties as $name => $definition){
			$column_name = "{$this::DB_PREFIX}_{$property}"; # on select requests, column names are prefixed

			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->$name = $row[$column_name]; # for primitive or identifier types, just copy the value

			} else if($definition->type_is('object')){
				if($definition->supclass_is(DataType::class)){
					// TODO


				} else if($definition->supclass_is(DataObject::class)){
					# check whether an object was referenced by checking the column referring to the object
					# that column should contain an id or null, which is from now on stored as $id
					if(empty($id = $row["{$column_name}_id"])){
						# no object was referenced, set the property to null
						$this->$name = null;
						continue;
					}

					# only if this is a Relation: if the relation was joined to a base/pivot object, use it for this
					# property if it is the correct class and id // TODO improve this explaination
					if($relation_base_object?->id === $id){
						$this->$name = $relation_base_object;
						continue;
					}

					# create a new object of the defined class and load it
					$this->$name = new {$definition->get_class()}();
					$this->$name->load($row, relations:false);

				} else if($definition->supclass_is(DataObjectRelationList::class)){
					if(!$relations){ # relations are disabled by the argument
						$this->$name = null;
						continue;
					}

					# create and set the relationlist and let it load the relations
					$this->$name = new {$definition->get_class()}();
					$this->$name->load($data, &$this);

				} else if($definition->supclass_is(DataObjectCollection::class)){
					// TODO


				}
			}
		}

		$this->load_custom_properties($row); # call the custom loading function to load custom properties
	}


	protected function load_custom_properties(array $row) : void {}


	// @throws: PropertyValueException, InputFailedException;
	final public function edit_property(string $name, mixed $input) : void {
		$this->cycle->check_step('edited');

		$definition = $this->properties[$name] ?? null;

		if(empty($definition)){
			throw new Exception("No PropertyDefinition found for property »{$name}«.");
		}

		# set a variable for the current value to check (later) whether the value actually has changed
		if($this->db->is_local()){
			# if the object is not yet in the database, it might have uninitialized properties. therefore just set
			# the variable to null, as it actually does not matter whether local objects have been altered or not
			$old_value = null;
		} else {
			# for objects that are not local, meaning they are already stored in the database and fully initialized
			$old_value = $this->$name;
		}


		if($definition->type_is('custom')){
			if($this->edit_custom_property($name, $input) === true){
				$this->db->set_altered();
			}

		} else if($definition->type_is('identifier')){ # handle properties of type identifier
			# check whether the current property is an id
			if($definition->class_is('id')){
				# check whether the id is tried to be altered. this is not allowed, as ids are generated upon creation
				# of the object. throw an Exception if that is the case. else just return as nothing else should happen.
				if($input !== $old_value){
					throw new IdentifierMismatchException($definition, $this, $input);
				} else {
					return;
				}
			}

			# check whether the property is tried to be altered despite not being alterable and not being local
			# (check is_local also because on local objects, even not-alterable properties must be settable)
			# if so, e.g. the property is tried to be altered illegaly, throw an Exception
			if(!$this->db->is_local() && $input !== $old_value && !$definition->is_alterable()){
				throw new PropertyNotAlterableException($definition, $this, $input);
			}

			if($input !== $old_value){
				# check whether the input is empty. identifiers must never be empty.
				if(empty($input)){
					throw new MissingValueException($definition);
				}

				# check whether the input matches the defined constraints given
				$definition->validate_input($input);

				# check whether the input is already set as an identifier on another object
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
			}

		} else if($definition->type_is('primitive')){ # handle properties of type primitive
			# alterability check, same as for identifier properties
			if(!$this->db->is_local() && $input !== $old_value && !$definition->is_alterable()){
				throw new PropertyNotAlterableException($definition, $this, $input);
			}

			# if input is empty, try setting it to null
			if(empty($input)){
				try {
					$this->$name = null;
					return;
				} catch(TypeError $e){
					# setting the property value to null failed -> property is not allowed to be empty
					throw new MissingValueException($def);
				}
			}

			# check whether the input matches the defined constraints
			$definition->validate_input($input);

			# if input is a string, escape html characters first
			$escaped_input = is_string($input) ? htmlspecialchars($input) : $input;

			# check whether the property value has been altered
			# set the property value to the input
			if($escaped_input !== $old_value){
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
				# as an id of a DataObject in the database or as an array of data to create a new DataObject from.

				if($input instanceof DataObject){ # input is an already loaded DataObject object
					# check whether the input object is of the correct class
					if(!$definition->class_is($input::class)){
						throw new IllegalValueException($definition, $input, 'wrong class');
					}

					# check whether the property value changed by comparing the current and the new objects' ids
					# if there is no difference, don't change the property at all
					if($input->id !== $old_value?->id){
						if(!$this->db->is_local() && !$definition->is_alterable()){
							throw new PropertyNotAlterableException($definition, $this, $input);
						}

						$this->$name = $input;
						$this->db->set_altered();
					}

				} else if(is_string($input) || (is_array($input) && !empty($input['id']))){ # input is an id
					# the input of an id can have two different forms: firstly, it can simply be a string with the id,
					# or secondly, it can be an array containing a key 'id' with the value being a string with the id.

					$id = $input['id'] ?? $input; # unify both input forms into a single variable
					$object = new {$definition->get_class()}();

					# check whether an object of the prescribed class with this id exists
					try {
						$object->pull($id);
					} catch(EmptyResultException $e){
						# no object with this id was found; throw an exception
						throw new RelationObjectNotFoundException($definition, $id);
					}

					# check whether the property value actually changed; only if so, edit the property
					if($object->id !== $old_value?->id){
						if(!$this->db->is_local() && !$definition->is_alterable()){
							throw new PropertyNotAlterableException($definition, $this, $input);
						}

						$this->$name = $object;
						$this->db->set_altered();
					}

				} else if(is_array($input)){ # input is an array that should contain data to create a new DataObject
					if(!$this->db->is_local() && !$definition->is_alterable()){
						throw new PropertyNotAlterableException($definition, $this, $input);
					}

					$object = new {$definition->get_class()}(); # construct a new object of the prescribed class
					$object->create(); # try to initialize (create) the object

					# try to fill the new object with the received data
					# MAY THROW an InputFailedException that will need to be handled by the function calling this here
					$object->receive_input($input);

					# everything went fine, edit the property
					$this->$name = $object;
					$this->db->set_altered();

				} else if(empty($input)){ # input is empty or null
					# try to set the property value to null
					try {
						$this->$name = null;
					} catch(TypeError $e){
						# the property cannot be null, throw an exception
						throw new MissingValueException($definition);
					}

					if($old_value !== null){
						if(!$definition->is_alterable()){
							throw new PropertyNotAlterableException($definition, $this, $input);
						}

						$this->db->set_altered();
					}

				} else { # unsupported format, throw an exception
					throw new PropertyValueException($definition, 'Unsupported input format.', $input);
				}

			} else if($definition->supclass_is(DataObjectRelationList::class)){
				# relationlists cannot be edited if no relations were pulled, for example because the object was
				# joined instead of being pulled independently. then the relationlist property is set to null
				if(!$this->db->is_local() && is_null($this->$name)){
					return;
				}

				if(!isset($this->$name)){ # if the relationlist does not yet exist, create it and load it empty
					$this->$name = new {$definition->get_class()}();
					$this->$name->load([], &$this);
				}

				$this->$name->receive_input($input);

			} else if($definition->supclass_is(DataObjectCollection::class)){
				throw new Exception('Collections are not yet supported.'); // TEMP
			}
		}

		$this->cycle->step('edited');
	}

	protected function edit_custom_property(PropertyDefinition $definition, mixed $input) : bool {} // (return value is whether the property was altered)


	protected function get_push_values() : array {
		$result = [];

		foreach($this->properties as $name => $definition){
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


	protected function get_custom_push_values() : array {}


	# this function disables the database access for this object and all other objects it contains.
	# it should be called by all controllers handing over this object to templates etc. in order to output it.
	# this is a safety feature that prevents templates from altering or deleting object data
	final public function freeze() : void {
		if($this->cycle->is_at('freezing')){
			return;
		}

		$this->cycle->step('freezing');
		$this->db->disable();

		foreach($this->properties as $name => $definition){
			if(!isset($this->name)){
				// TODO this is a problem! uninitialized property!
			}

			if($definition->type_is('object') && !$definition->supclass_is(DataType::class)){
				$this->$name?->freeze();
			}
		}

		$this->cycle->step('frozen');
	}


	# this function transforms this object into an array containing all of its properties.
	# properties that are objects themselves get also transformed into arrays using theír own arrayify functions.
	final public function arrayify() : array|null {
		if($this->cycle->is_at('freezing')){
			return null;
		}

		$this->cycle->step('freezing');
		$this->db->disable();

		$result = [];

		foreach($this->properties as $name => $definition){
			if(!isset($this->$name)){
				$result[$name] = null;
			} else if($definition->type_is('object') && !$definition->supclass_is(DataType::class)){
				$result[$name] = $this->$name?->arrayify();
			} else {
				$result[$name] = $this->$name;
			}
		}

		$this->cycle->step('frozen');

		return $result;
	}


	function __get(string $name) : mixed {
		if(in_array($name, $this::PROPERTIES)){
			return $this->$name;
		}
	}

	function __set(string $name, mixed $value) : void {
		if(in_array($name, $this::PROPERTIES)){
			$this->edit_property($name, $value);
		} else {
			// TODO throw Exception
			throw new Exception();
		}
	}

	function __isset(string $name) : bool {
		if(in_array($name, $this::PROPERTIES)){
			return isset($this->$name);
		}
	}

	function __unset(string $name) : void {
		if(in_array($name, $this::PROPERTIES)){
			$this->edit_property($name, null);
		}
	}

}
?>
