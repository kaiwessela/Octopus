<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\AndCondition;
use \Octopus\Core\Model\Database\Requests\Conditions\OrCondition;
use \Exception;

# This trait shares common methods for Entity and Relationship that relate to the loading, altering, validating and
# outputting of attributes.

trait EntityAndRelationship {

	final protected function load_attributes() : void {
		static::$attributes = [];

		foreach(static::define_attributes() as $name => $attribute){
			$this->$name = $attribute;
			$this->$name->bind($name, $this);
			static::$attributes[] = $name;
		}
	}


	public function is_new() : bool {
		return $this->is_new;
	}


	public function is_loaded() : bool {
		return isset($this->is_new);
	}


	public function is_independent() : bool {
		return !isset($this->context);
	}


	final public function get_attribute(string $name) : Attribute {
		if(in_array($name, static::$attributes)){
			return $this->$name;
		} else {
			throw new Exception("Attribute «{$name}» not found.");
		}
	}


	final public function get_main_identifier_attribute() : IdentifierAttribute {
		return $this->get_attribute($this->main_identifier);
	}


	final public function build_pull_request(Request &$request, array $attributes = []) : void {
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

			if($this->$name->is_pullable() && $pull){
				$request->add($this->$name);
			}

			if($this->$name->is_joinable() && $join){
				if(!$this->is_independent() && $this->$name->get_class() === $this->context::class){
					continue;
				}

				if(!$this->$name->is_pullable() && !($this->is_independent() || $this->context instanceof EntityList)){ // TEMP
					continue;
				}

				if($this->$name instanceof EntityAttribute){
					$request->add_join($this->$name->get_join_request($option));
					// $request->add_join($this->$name->get_prototype()->join(on:$this->$name, attributes:$option));
				} else if($this->$name instanceof RelationshipAttribute){
					$request->add_join($this->$name->get_join_request($option));
					// $request->add_join($this->$name->get_prototype()->join(on:$this->id, attributes:$option));
				}
			}
		}
	}


	final public function resolve_pull_conditions(array $options, string $mode = 'AND') : ?Condition {
		$conditions = [];

		foreach($options as $attribute => $option){
			if(is_int($attribute)){ # resolve listed options (like in an AND/OR chain)
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$conditions[] = $this->resolve_pull_conditions($option);

			} else if($attribute === 'AND' || $attribute === 'OR'){
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$conditions[] = $this->resolve_pull_conditions($option, $attribute);

			} else if(in_array($attribute, static::$attributes)){
				$conditions[] = $this->$attribute->resolve_pull_condition($option);

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
		} else {
			throw new Exception(); // TODO
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


	final public function resolve_pull_order(array $options) : array {
		$level0 = [];
		$level1 = [];
		$level2 = [];

		foreach($options as $attribute => $option){
			if(!in_array($attribute, static::$attributes)){
				throw new Exception(); // Error
			}

			if($this->$attribute instanceof PropertyAttribute){
				if($option === 'ascending' || $option === 'ASC' || $option === '+'){
					$level0[] = [$this->$attribute, 'ASC'];
				} else if($option === 'descending' || $option === 'DESC' || $option === '-'){
					$level0[] = [$this->$attribute, 'DESC'];
				} else {
					throw new Exception();
				}

			} else if($this->$attribute instanceof EntityAttribute){
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$level1 = [...$level1, ...$this->$attribute->get_prototype()->resolve_pull_order($option)];
			} else if($this->$attribute instanceof RelationshipAttribute){
				if(!is_array($option)){
					throw new Exception(); // Error
				}

				$level2 = [...$level2, ...$this->$attribute->get_prototype()->resolve_pull_order($option)];
			}
		}

		if(empty($level0) && !empty($level2)){
			$level0[] = [$this->id, 'ASC'];
		}

		return [...$level0, ...$level1, ...$level2];
	}
	/*
	posts
	[
		'timestamp' => 'ascending'|'ASC'|'+',
		'columns' => [
			'name' => 'descending'|'DESC'|'-'
		]
	]
	*/


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


	final public function get_db_table() : string {
		return static::DB_TABLE;
	}


	final public function get_prefixed_db_table() : string {
		if(isset($this->db_prefix)){
			return "{$this->db_prefix}~{$this->get_db_table()}";
		} else {
			return $this->get_db_table();
		}
	}


	final function __clone() {
		foreach(static::$attributes as $name){
			$this->$name = clone $this->$name;
		}
	}


	function __get($name) {
		# if $this->$name is a defined attribute, return its value
		if(in_array($name, static::$attributes) && $this->$name->is_loaded()){
			return $this->$name->get_value();
		}
	}


	function __isset(string $name) : bool {
		return in_array($name, static::$attributes) && $this->$name->is_loaded();
	}
}
?>
