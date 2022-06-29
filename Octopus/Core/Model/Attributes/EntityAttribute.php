<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\PullableAttributes;
use \Octopus\Core\Model\Attributes\JoinableAttributes;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Model\Attributes\Exceptions\EntityNotFoundException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\EqualsCondition;
use \Exception;

final class EntityAttribute extends Attribute {
	# inherited from Attribute
	# protected Entity|Relationship $parent;
	# protected string $name;
	# protected bool $is_loaded;
	# protected bool $is_required;
	# protected bool $is_editable;
	# protected bool $is_dirty;
	# protected mixed $value;

	protected string $class;
	protected Entity $prototype;


	# ---> Attribute
	# final public function bind(string $name, Entity|Relationship $parent) : void;
	# final public function is_loaded() : bool;
	# final public function is_required() : bool;
	# final public function is_editable() : bool;
	# final public function is_dirty() : bool;
	# final public function get_name() : string;
	# final public function get_db_table() : string;
	# final public function get_prefixed_db_table() : string;
	# final public function &get_value() : mixed;
	# public function is_empty() : bool;

	use PullableAttributes;
	# final public function is_pullable();
	# final public function get_prefixed_db_column() : string;
	# final public function get_result_column() : string;

	use JoinableAttributes;
	# final public function is_joinable() : bool;
	# final public function get_class() : string;
	# final public function get_detection_column() : string;



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


	final public function load(array $data) : void {
		if(is_null($data[$this->get_detection_column()])){
			$this->value = null;
		} else {
			$this->value = clone $this->get_prototype();
			$this->value->load($data);
		}

		$this->loaded = true;
	}


	final public function edit(mixed $input) : void {
		if($input instanceof Entity){
			if($input::class !== $this->get_class()){
				throw new IllegalValueException($this, $input, 'wrong class');
			}

			$entity = $input;

		} else if(is_string($input) || (is_array($input) && isset($input['id']))){ // FIXME
			$id = $input['id'] ?? $input;
			$entity = clone $this->get_prototype();

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


	final public function get_prototype() : Entity {
		if(!isset($this->prototype)){
			$class = $this->get_class();
			$this->prototype = new $class($this->parent, null, $this->get_prefixed_db_column());
		}

		return $this->prototype;
	}


	final public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_string($option)){
			return new EqualsCondition($this, $option);
		} else if(is_array($option)){
			return $this->get_prototype()->resolve_pull_conditions($option);
		} else {
			throw new Exception('invalid option.'); // TODO
		}
	}
}
?>
