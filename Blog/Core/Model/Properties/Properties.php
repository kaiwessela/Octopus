<?php
namespace Blog\Model\Abstracts\Traits;

# This trait takes care about all standard operations for properties of DataObjects and friends,
# which means: Definition, Validation, Transformation
# It heavily uses the PropertyDefinition class, so it may be wise to take a look on that too.

trait Properties {
	# This trait requires the following constants in every class using it:
	# const DB_PREFIX;
	# const PROPERTIES;
	# final const ALLOWED_PROPERTY_TYPES;

	// TODO check property types with ALLOWED_PROPERTY_TYPES


	# Generate and return a random value that is set as the object's id upon creation
	# The id is 8 characters long and consists of these characters: 0123456789abcdef (hexadecimal/base16)
	final protected static function generate_id() : string {
		return bin2hex(random_bytes(4)); # first generate 4 random bytes, then turn them into a hexadecimal string
	}


	final protected function load_properties(array $data, bool $norelations, bool $nocollections) : void {
		// TODO explaination, do better
		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		foreach($this::PROPERTIES as $property => $definition){
			$column_name = $this::DB_PREFIX.'_'.$property;
			$def = new PropertyDefinition($name, $definition);

			if($def->type == 'primitive' || $def->type == 'special'){
				$this->$property = $row[$column_name];
			} else if($def->type == 'custom'){
				$this->load_custom_property($property, $def, $data);
			} else if($def->type == 'object'){
				$value = $row[$column_name.'_id'];
				$class = $def->class;

				if(empty($value)){
					$this->$property == null;
					continue;
				}

				if($def->supclass_is(DataType::class)){
					$this->$property = new $class($value);
				} else if($def->supclass_is(DataObject::class)){
					$this->$property = new $class();
					$this->$property->load($data);
				} else if($def->supclass_is(DataObjectRelationList::class)){
					if($norelations === true){
						$this->$property = null;
						continue;
					}

					$this->$property = new $class();
					$this->$property->load($data, $this);
				} else if($def->supclass_is(DataObjectCollection::class)){
					// TODO
				} else {
					// Error
				}
			}
		}
	}


	protected function load_custom_property(string $name, mixed $value, ?PropertyDefinition $def = null) : void {}


	final public function edit_property(string $name, mixed $input) : void {
		$this->cycle->check_step('edit');

		// exceptions expected: PropertyValueException, InputFailedException
		if(empty($this::PROPERTIES[$name])){
			throw new Exception("Property '$name' not found.");
		}

		$def = new PropertyDefinition($name, $this::PROPERTIES[$name]);

		try {
			$old_value = $this->$name;
		} catch(Error $e){
			# the property is not yet initialized
			$old_value = null;
			# this theoretically could cause problems when setting the property to null, because then
			# $old_value === $this->$name, thus $this->db->set_altered() is not called despite the fact that the
			# property is indeed altered. However, this is not an issue, as uninitialized values only occur
			# when the object is freshly created (db->is_local()) and not yet stored in the database, whereby
			# $this->db->set_altered() has no effect anyway.
		}

		if($def->type_is_custom()){
			$this->edit_custom_property($name, $input); // TODO set altered

		} else if($def->type_is_special()){
			if($this->db->is_local()){
				if($def->name === 'id'){
					return;
				} else if($def->name === 'longid'){
					if(empty($input)){
						throw new MissingValueException($def);
					}

					$def->validate_input($input);

					try {
						$existing = new $this;
						$existing->pull($input);
						throw new IdentifierCollisionException($def, $existing);
					} catch(EmptyResultException $e){
						$this->$name = $input;
					}
				}
			} else if($input !== $this->$name){ # db->is_local === false
				throw new IdentifierMismatchException($def, $this, $input);
			}

			if($this->$name !== $old_value){
				$this->db->set_altered();
			}

		} else if($def->type_is_primitive()){
			if(empty($input)){
				try {
					$this->$name = null;
					return;
				} catch(TypeError $e){
					throw new MissingValueException($def);
				}
			}

			$def->validate_input($input);

			$this->$name = is_string($input) ? htmlspecialchars($input) : $input;

			if($this->$name !== $old_value){
				$this->db->set_altered();
			}

		} else if($def->type_is_object()){
			$class = $def->class;

			if($def->supclass_is(DataType::class)){
				try {
					$this->$name = $class::import($input);
				} catch(InputException $e){ // TODO this is outdated
					$e->field = $name;
					throw $e;
				}

				// TODO altered

			} else if($def->supclass_is(DataObject::class)){
				if($input instanceof DataObject){
					$input->push(); // TODO check exceptions on this
					$this->$name = $input;

				} else if(is_string($input) || (is_array($input) && !empty($input['id']))){
					$id = $input['id'] ?? $input;
					$object = new $class();

					try {
						$object->pull($id);
						$this->$name = $object;
					} catch(EmptyResultException $e){
						throw new RelationObjectNotFoundException($def, $id);
					}

				} else if(is_array($input)){
					$object = new $class();
					$object->create();
					$object->edit($input);
					$object->push();
					$this->$name = $object;

				} else {
					try {
						$this->$name = null;
					} catch(TypeError $e){
						throw new MissingValueException($def);
					}
				}

				if($this->$name?->id !== $old_value?->id){
					$this->db->set_altered();
				}

			} else if($def->supclass_is(DataObjectRelationList::class)){
				// TODO
			} else if($def->supclass_is(DataObjectCollection::class)){
				// TODO
			}
		}

		$this->cycle->step('edit');
	}

	protected function edit_custom_property(string $name, mixed $input, ?PropertyDefinition $def = null) : void {}


	protected function get_push_values() : array {
		// TODO handle uninitialized properties

		$result = [];

		foreach($this::PROPERTIES as $name => $definition){
			$def = new PropertyDefinition($name, $definition);

			if($def->type_is_custom()){
				$result = $this->get_custom_push_values($name) + $result;
			} else if($def->type_is_special()){
				if($def->class === 'id'){
					$result[$name] = $this->id;
				} else if($def->class === 'longid' && $this->db->is_local()){
					$result[$name] = $this->longid;
				}
			} else if($def->type_is_primitive()){
				$result[$name] = $this->$name;
			} else if($def->type_is_object()){
				if($def->supclass_is(DataType::class)){
					// TODO
				} else if($def->supclass_is(DataObject::class)){
					$result[$name.'_id'] = $this->$name?->id;
				} else if($def->supclass_is(DataType::class)){
					// TODO
				}
			}
		}

		return $result;
	}


	protected function get_custom_push_values(string $property) : array {}



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
