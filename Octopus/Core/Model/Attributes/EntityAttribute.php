<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\EntityNotFoundException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Exception;

final class EntityAttribute extends Attribute {
	protected string $class;


	final public static function define(string $class, bool $required = false, bool $editable = true) : EntityAttribute {
		if(!class_exists($class) || !is_subclass_of($class, Entity::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		$attr = new EntityAttribute();
		$attr->required = $required;
		$attr->editable = $editable;
		$attr->class = $class;
		return $attr;
	}


	final public function load(Entity|array $data) : void {
		if($data instanceof Entity){ // IDEA
			$this->value = $value;
		} else if(is_null($data[$this->get_prefixed_db_column()])){
			$this->value = null;
		} else {
			$class = $this->get_class();
			$this->value = new $class($this->parent, null, $this->get_name());
			$this->value->load($data);
		}

		$this->loaded = true;
	}


	final public function edit(mixed $input) : void {
		$class = $this->get_class();

		if($input instanceof Entity){
			if($input::class !== $class){
				throw new IllegalValueException($this, $input, 'wrong class');
			}

			$entity = $input;

		} else if(is_string($input) || (is_array($input) && isset($input['id']))){ // FIXME
			$id = $input['id'] ?? $input;
			$entity = new $class($this->parent);

			try {
				$entity->pull($id);
			} catch(EmptyResultException $e){
				throw new EntityNotFoundException($this, $id);
			}
		} else if(empty($input)){
			if($this->is_required()){
				throw new MissingValueException($this);
			} else {
				$entity = null;
			}
		} else {
			throw new AttributeValueException($this, 'Unsuppoted input format.', $input);
		}

		if($entity?->id !== $this->value?->id){
			if(!$this->is_editable()){
				throw new AttributeNotAlterableException($this, $this, $entity); // FIXME
			}

			$this->value = $entity;
			$this->edited = true;
		}
	}


	final public function get_db_column() : string {
		return $this->name.'_id';
	}


	final public function get_push_value() : null|string|int|float {
		return $this->value?->id;
	}


	final public function get_class() : string {
		return $this->class;
	}


	// public function get_join() : JoinRequest {
	//
	// }
}
?>
