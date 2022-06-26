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


	final public function build_request(Request &$request, array $attributes = [], array $conditions = []) : void {
		foreach($attributes as $name => $option){
			if(!in_array($name, static::$attributes)){
				throw new Exception(); // Error
			}

			if(!is_null($option) && !is_bool($option) && !(is_array($option) && $this->$name->is_joinable())){
				throw new Exception(); // Error
			}
		}

		foreach(static::$attributes as $name){
			$option = $attributes[$name] ?? static::DEFAULT_PULL_ATTRIBUTES[$name] ?? null;

			if(is_array($option) || is_null($option)){
				$pull = true;
				$join = true;
			} else if($option === true){
				$pull = true;
				$join = false;
			} else if($option === false){
				$pull = false;
				$join = false;
			} else {
				throw new Exception(); // Error
			}

			if($attribute->is_pullable() && $pull){
				$request->add($this->$name);
			}

			if($attribute->is_joinable() && $join && ($this->$name->get_class() !== $this->context::class)){
				$request->add_join($this->$name->get_prototype()->join()); // TODO
			}
		}

		if(!empty($conditions)){
			$request->set_condition($this->resolve_conditions($conditions));
		}

		// TODO order
	}


	final public function resolve_conditions(array $options, bool $mode = 'AND') : ?Condition {
		$conditions = [];

		foreach($options as $attribute => $option){
			if(is_int($attribute)){ # resolve listed options (like in an AND/OR chain)
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$conditions[] = $this->resolve_conditions($option);

			} else if($attribute === 'AND' || $attribute === 'OR'){
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$conditions[] = $this->resolve_conditions($option, $attribute);

			} else if(in_array($attribute, static::$attributes)){
				$conditions[] = $this->$attribute->resolve_condition($option);

			} else {
				throw new Exception(); // Error
			}
		}

		if(empty($conditions)){
			return null;
		} else if(count($conditions) === 1){
			return $condition;
		} else if($mode === 'OR'){
			return new OrCondition(...$conditions);
		} else if($mode === 'AND'){
			return new AndCondition(...$conditions);
		}
	}
	/*
	posts
	[
		'columns' => [
			'OR' => [
				[
					'id' => 'test',
				],
				[
					'id' => 'abc',
				]
			]

		]
	]
	*/


	final protected function load_attributes() : void {
		static::$attributes = [];

		foreach(static::define_attributes() as $name => $attribute){
			$this->$name = $attribute;
			$this->$name->bind($name, $this);
			static::$attributes[] = $name;
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

		if($this->$name instanceof RelationshipAttribute){
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
		if(isset(static::$attributes[$name])){
			return $this->$name->get_value();
		}
	}


	function __isset(string $name) : bool {
		return isset(static::$attributes[$name]);
	}
}
?>
