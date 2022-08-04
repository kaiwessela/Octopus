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
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\Equals;
use \Octopus\Core\Model\Database\Requests\Conditions\InList;
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
	protected bool $entity_must_exist;
	protected string $identify_by;


	# ---> Attribute
	# final public function bind(string $name, Entity|Relationship $parent) : void;
	# final public function is_loaded() : bool;
	# final public function is_required() : bool;
	# final public function is_editable() : bool;
	# final public function is_dirty() : bool;
	# final public function set_clean() : void;
	# final public function get_name() : string;
	# final public function get_db_table() : string;
	# final public function get_prefixed_db_table() : string;
	# final public function &get_value() : mixed;
	# public function is_empty() : bool;

	use PullableAttributes;
	# final public function is_pullable();
	# final public function get_db_column();
	# final public function get_prefixed_db_column() : string;
	# final public function get_result_column() : string;

	use JoinableAttributes;
	# final public function is_joinable() : bool;
	# final public function get_class() : string;
	# final public function get_detection_column() : string;



	final public static function define(string $class, string $identify_by, bool $is_required = false, bool $is_editable = true, bool $entity_must_exist = true) : EntityAttribute {
		if(!class_exists($class) || !is_subclass_of($class, Entity::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		$attribute = new static($is_required, $is_editable);
		$attribute->class = $class;
		$attribute->entity_must_exist = $entity_must_exist;
		$attribute->identify_by = $identify_by; // TODO validate

		return $attribute;
	}


	final public function load(Entity|array $data) : void {
		if($data instanceof Entity){
			$this->value = $data; // TODO validate this
		} else if(is_null($data[$this->get_result_column()])){
			$this->value = null;
		} else if(!array_key_exists($this->get_detection_column(), $data)){
			$this->value = clone $this->get_prototype();
			$this->value->load([$this->get_detection_column() => $data[$this->get_result_column()]]);
		} else {
			$this->value = clone $this->get_prototype();
			$this->value->load($data);
		}

		$this->is_loaded = true;
	}


	final public function edit(mixed $input) : void {
		if($input instanceof Entity){
			if($input::class !== $this->get_class()){
				throw new IllegalValueException($this, $input, 'wrong class');
			} else if(!$input->is_loaded()){
				throw new IllegalValueException($this, $input, 'entity is not loaded');
			}

			$entity = $input;
			$identifier = $input->{$this->identify_by};
		} else if(is_string($input)){
			$identifier = $input;
			$entity = clone $this->get_prototype();

			try {
				$entity->pull($identifier, $this->identify_by, [$this->identify_by => true]);
			} catch(EmptyResultException $e){
				if($this->entity_must_exist){
					throw new EntityNotFoundException($this, $identifier);
				} else {
					// TODO validate the identifier
					$entity->load([$this->get_detection_column() => $identifier]);
				}
			}
		} else if(empty($input)){
			if($this->is_required()){
				throw new MissingValueException($this);
			} else {
				$entity = null;
				$identifier = null;
			}
		} else {
			throw new AttributeValueException($this, $input, 'unsuppoted input format.');
		}

		if($identifier !== $this->value?->{$this->identify_by}){
			if(!$this->is_editable()){
				throw new AttributeNotAlterableException($this, $identifier);
			}

			$this->value = $entity;
			$this->set_dirty();
		}
	}


	final public function get_push_value() : null|string|int|float {
		return $this->value?->{$this->identify_by};
	}


	final public function get_prototype() : Entity {
		if(!isset($this->prototype)){
			$class = $this->get_class();
			$this->prototype = new $class($this->parent, $this->parent->get_db(), $this->get_result_column());
		}

		return $this->prototype;
	}


	final public function get_detection_column() : string {
		return "{$this->get_prototype()->get_prefixed_db_table()}.{$this->identify_by}";
	}


	final public function get_join_request(array $attributes = []) : JoinRequest {
		return $this->get_prototype()->join(on:$this, identify_by:$this->identify_by, attributes:$attributes);
	}


	final public function get_identify_by() : string { // this could be nicer maybe
		return $this->identify_by;
	}


	final public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_string($option)){
			return new Equals($this, $option);
		} else if(is_array($option)){
			if(array_is_list($option)){
				foreach($option as $opt){
					if(!is_string($opt)){
						throw new Exception('invalid condition.'); // TODO
					}
				}

				return new InList($this, $option);
			} else {
				return $this->get_prototype()->resolve_pull_conditions($option);
			}
		} else {
			throw new Exception('invalid condition.'); // TODO
		}
	}


	final public function arrayify() : null|string|int|float|bool|array {
		return $this->value?->arrayify();
	}
}
?>
