<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Exception;

class EntityAttribute extends Attribute {
	# inherited from Attribute:
	# protected Entity $parent;
	# protected string $name;
	# protected ?string $db_column;
	# protected mixed $value;
	# protected bool $editable;
	# protected bool $required;

	protected string $class;


	public function init(Entity $parent, string $name) : void {
		parent::init($parent, $name);

		$this->db_column = "{$name}_id";
	}


	public static function define(string $class, bool $required = false, bool $editable = true) : EntityAttribute {
		$attribute = new EntityAttribute();

		if(!class_exists($class)){
			throw new Exception("Attribute «{$name}»: class «{$class}» does not exist.");
		}

		if(!is_subclass_of($class, Entity::class)){
			throw new Exception("Attribute «{$name}»: class «{$class}» is not an Entity.");
		}

		$attribute->required = $required;
		$attribute->editable = $editable;
	}


	public function load(mixed $value) : void {
		if(empty($value)){
			$this->value = null;
		} else {
			$cls = $this->class;
			$this->value = new $cls($this->parent);
			$this->value->load($row); // FIXME
		}
	}


	public function edit(mixed $input) : void {
		# an Entity can be received in various ways: as an already loaded instance of Entity,
		# as an id of an entity in the database or as an array with data to create a new entity from.
		// NOTE the last way is no more supported to prevent side effects.

		if($input instanceof Entity){ # input is an already loaded instance of Entity
			# check whether the input object is of the correct class
			if($this->class !== $input::class){
				throw new IllegalValueException($this, $input, 'unmatching class');
			}

			$object = $input;

		} else if(is_string($input) || (is_array($input) && !empty($input['id']))){ # input is an id
			# the input of an id can have two different forms: first, it can simply be a string with the id,
			# or second, it can be an array containing a key 'id' with the value being a string with the id.

			$id = $input['id'] ?? $input; # unify both input formats into a single variable
			$cls = $this->class;
			$object = new $cls($this->parent);

			# check whether an entity of the prescribed class with this id exists
			try {
				$object->pull($id);
			} catch(EmptyResultException $e){
				# no entity with this id was found; throw an exception
				throw new EntityNotFoundException($this, $id);
			}

		} else if(empty($input)){ # input is empty or null
			if($this->is_required()){ # if the attribute is required to be set, throw an error
				throw new MissingValueException($this);
			} else { # otherwise just set it to null
				$this->value = null;
			}

		} else { # unsupported format, throw an exception
			throw new AttributeValueException($this, 'unsupported input format', $input);
		}

		# check whether the attribute value changed by comparing the current and the new entities’ ids
		# if there is no difference, don't change the attribute at all
		if($object?->id !== $this->value?->id){
			if(!is_null($this->value) && !$this->is_alterable()){
				throw new AttributeNotAlterableException($definition, $this, $object);
			}

			$this->value = $object;
		}
	}


	protected function return_push_value() : null|string|int|float {
		return $this->value?->id;
	}
}
?>
