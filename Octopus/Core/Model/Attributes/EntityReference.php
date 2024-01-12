<?php
namespace Octopus\Core\Model\Attributes;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Octopus\Core\Model\Attributes\Exceptions\EntityNotFoundException;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use Octopus\Core\Model\Attributes\Joinable;
use Octopus\Core\Model\Attributes\Pullable;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Conditions\Equals;
use Octopus\Core\Model\Database\Conditions\InList;
use Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use Octopus\Core\Model\Database\Requests\Join;
use Octopus\Core\Model\Entity;

final class EntityReference extends Attribute {
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

	use Pullable;
	# final public function is_pullable();
	# final public function get_db_column();
	# final public function get_prefixed_db_column() : string;
	# final public function get_result_column() : string;

	use Joinable;
	# final public function is_joinable() : bool;
	# final public function get_class() : string;
	# final public function get_detection_column() : string;



	final public static function define(string $class, string $identify_by, bool $is_required = false, bool $is_editable = true, bool $entity_must_exist = true) : EntityReference {
		if(!class_exists($class) || !is_subclass_of($class, Entity::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		$attribute = new static($is_required, $is_editable);
		$attribute->class = $class;
		$attribute->entity_must_exist = $entity_must_exist;
		$attribute->identify_by = $identify_by; // TODO validate

		return $attribute;
	}


	final public function load(Entity|array|null $data) : void {
		if(is_null($data)){
			$this->value = null;
		} else if($data instanceof Entity){
			$this->value = $data; // TODO validate this
		} else if(is_null($data[$this->get_result_column()])){
			$this->value = null;
		} else if(empty($data[$this->get_detection_column()])){
			$this->value = clone $this->get_prototype();
			$this->value->load([$this->get_detection_column() => $data[$this->get_result_column()]]);
		} else {
			$this->value = clone $this->get_prototype();
			$this->value->load($data);
		}

		$this->is_loaded = true;
	}


	final protected function _edit(mixed $input) : void {
		if($input instanceof Entity){
			if($input::class !== $this->get_class()){
				throw new IllegalValueException($this, $input, 'wrong class');
			} else if(!$input->is_loaded()){
				throw new IllegalValueException($this, $input, 'entity is not loaded');
			}

			$this->value = $input;
		} else if(empty($input)){
			$this->value = null;
		} else if(is_string($input)){
			$identifier = $input;
			$entity = clone $this->get_prototype();

			try {
				$entity->pull($identifier, $this->identify_by, [$this->identify_by => true]);
			} catch(EmptyResultException $e){
				if($this->entity_must_exist()){
					throw new EntityNotFoundException($this, $identifier);
				} else {
					// TODO validate the identifier
					$entity->load([$this->get_detection_column() => $identifier]);
				}
			}

			$this->value = $entity;
		} else {
			throw new AttributeValueException($this, $input, 'unsuppoted input format.');
		}
	}


	final public function equals(mixed $value) : bool {
		if(!is_null($value) && !$value instanceof Entity){
			return false;
		}

		return $this->value?->{$this->identify_by} === $value?->{$this->identify_by};
	}


	final public function get_push_value() : null|string|int|float {
		return $this->value?->{$this->identify_by};
	}


	final public function get_prototype() : Entity { // IMPROVE rename to get_entity_prototype
		if(!isset($this->prototype)){
			$class = $this->get_class();
			$this->prototype = new $class();
			$this->prototype->contextualize(entity:$this->parent, attribute:$this);
		}

		return $this->prototype;
	}


	final public function get_detection_column() : string {
		return "{$this->get_prototype()->get_prefixed_db_table()}.{$this->identify_by}";
	}


	final public function get_join_request(array $include_attributes) : Join {
		return $this->get_prototype()->join($this, $this->identify_by, $include_attributes);
	}


	final public function entity_must_exist() : bool {
		return $this->entity_must_exist;
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