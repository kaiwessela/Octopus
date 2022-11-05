<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\GeneratedIdentifierAttribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotLoadedException;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\AndOp;
use \Octopus\Core\Model\Database\Requests\Conditions\OrOp;
use \Exception;

# This trait shares common methods for Entity and Relationship that relate to the loading, altering, validating and
# outputting of attributes.

trait EntityAndRelationship {

	final protected function load_attributes() : void {
		if(!isset(self::$attributes)){
			self::$attributes = [];
		}

		self::$attributes[static::class] = [];

		// idea: store also the just defined attribute objects themselves in self::$attributes and just clone them for each new instance

		foreach(static::define_attributes() as $name => $attribute){
			$this->$name = $attribute;
			$this->$name->bind($name, $this);
			self::$attributes[static::class][] = $name;
		}
	}


	# Initialize a new entity that is not yet stored in the database
	# Generate a random id for the new entity and set all attributes to null
	final public function create() : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$this->is_new = true;

		foreach($this->get_attributes() as $name){
			$this->$name->load(null);

			if($this->$name instanceof GeneratedIdentifierAttribute){
				$this->$name->generate();
			}
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


	final public function get_attributes() : array {
		return self::$attributes[static::class];
	}


	final public function has_attribute(string|Attribute $attribute) : bool {
		if(is_string($attribute)){
			return in_array($attribute, $this->get_attributes());
		} else {
			return $attribute->parent_is($this);
		}
	}


	final public function get_attribute(string $name) : Attribute {
		if(in_array($name, $this->get_attributes())){
			return $this->$name;
		} else {
			throw new Exception("Attribute «{$name}» not found.");
		}
	}


	final public function get_main_identifier_attribute() : IdentifierAttribute {
		return $this->get_attribute($this->main_identifier);
	}


	/*
	how to deal with order:

	1. get input order chain: [1 => [title, +], 2 => [author:name, +], 3 => [categories:category:name?, +]]
	2. split it to get a chain for each select/join request, keep indices:
		articles (main/select): 1 => [title, +],
		authors (forward join): 2 => [name, +],
		categories (reverse join): 
			category (forward join): 3 => [name, +]
	3. pass these chains into the request

	In SelectRequest/JoinRequest, on resolve():
	4. merge the own chain and the chains of each forward join below (recursively) together, ordering after the index:
		articles: 1 => [title, +], 2 => [(author.)name, +]
		categories: 3 => [(categories.category.)name, +]
	5. reindex the chains:
		articles: 1 => ..., 2 => ...
		categories: 1 => ...
	6. append the default/fallback order (which is the main identifier) to the main chain and each backwards join's chain:
		articles: 1 => ..., 2 => ..., 3 => [id, +]
		categories: 1 => ..., 2 => [id, +]
	7. glue all chains together:
		1, 2, 3, 4, 5

	what if there are two reverse joins on the same level? idea: the one with the longer chain takes precedence
	*/

	final public function build_pull_request(Request &$request, array $include_attributes, array $order_by) : void {
		foreach($include_attributes as $name => $option){
			if(!$this->has_attribute($name)){
				throw new Exception("Unknown attribute «{$name}».");
			}

			if(!is_null($option) && !is_bool($option) && !(is_array($option) && $this->$name->is_joinable())){
				throw new Exception("Invalid format of inclusion directive for attribute «{$name}».");
			}
		}

		$order_chains = $this->split_pull_order_chain($order_by);

		foreach($this->get_attributes() as $name){
			$option = $include_attributes[$name] ?? static::DEFAULT_PULL_ATTRIBUTES[$name] ?? null;

			if(is_array($option)){
				$pull = true;
				$join = true;
			} else if($option === true || is_null($option)){
				$pull = true;
				$join = false;
			} else if($option === false){
				$pull = false;
				$join = false;
			} else {
				throw new Exception("Invalid format of default inclusion directive for attribute «{$name}».");
			}

			if($this->$name->is_pullable()){
				if($pull){
					$request->add($this->$name);
				}

				if(array_key_exists($name, $order_chains[0])){ // there is an order statement for the attribute
					if($pull){
						list($significance, $direction) = $order_chains[0][$name];

						$request->add_order($this->$name, $direction, $significance);
					} else {
						throw new Exception("Cannot order results by non-included attribute «{$name}».");
					}
				}
			} else if($pull){
				throw new Exception("Cannot add non-pullable attribute «{$name}» to the request.");
			}

			if($this->$name->is_joinable()){
				if(array_key_exists($name, $order_chains)){
					if($join){
						$order_chain = $order_chains[$name];
					} else {
						throw new Exception("Cannot order results by non-joined attribute «{$name}».");
					}
				} else {
					$order_chain = [];
				}

				if($join){
					if(!$this->is_independent() && $this->$name->get_class() === $this->context::class){ // TEMP
						continue;
					}
	
					if(!$this->$name->is_pullable() && !($this->is_independent() || $this->context instanceof EntityList)){ // TEMP
						continue;
					}

					$request->add_join($this->$name->get_join_request($option ?? [], $order_chain));
				}
			} else if($join){
				throw new Exception("Cannot join non-joinable attribute «{$name}» to the request.");
			}
		}
	}


	final protected function split_pull_order_chain(array $raw_chain) : array {
		if(!array_is_list($raw_chain)){
			throw new Exception("Invalid format of order directives list.");
		}
		
		$result = [
			0 => [] // 0 is simply used because there wont be any collision with a join request’s chain
		];

		// $order = [1 => ['attribute(.remainder)', 'direction'], 2 => ..., ...]
		// $significance is simply the index
		foreach($raw_chain as $significance => $order){
			// check format of each individual order
			if(!is_array($order) || !count($order) === 2 || !is_string($order[0]) || !is_string($order[1])){
				throw new Exception("Invalid format of order directive #{$significance}.");
			}

			list($full_attribute, $direction) = $order;

			$exp = explode('.', $full_attribute, 2);
			$attribute = $exp[0];
			$remainder = $exp[1] ?? '';

			if(!$this->has_attribute($attribute)){
				throw new Exception("Unknown attribute «{$attribute}» in order directive #{$significance}.");
			}

			$direction = match($direction){
				'+', 'ascending', 'ASC' => 'ASC',
				'-', 'descending', 'DESC' => 'DESC',
				default => throw new Exception("Invalid direction in order directive #{$significance}.")
			};

			if(empty($remainder)){
				if(!$this->$attribute->is_pullable()){
					throw new Exception("Cannot order results by non-pullable attribute {$attribute} (#{$significance}).");
				}

				$result[0][$attribute] = [$significance, $direction];
			} else {
				if(!$this->$attribute->is_joinable()){
					throw new Exception("Unknown attribute «{$attribute}» in order directive #{$significance}.");
				}

				if(!isset($result[$attribute])){
					$result[$attribute] = [];
				}

				$result[$attribute][$significance] = [$remainder, $direction];
			}
		}

		return $result;
	}


	final public function resolve_pull_conditions(array $options, string $mode = 'AND') : ?Condition { // TODO does not work on joins
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

			} else if($this->has_attribute($attribute)){
				$conditions[] = $this->$attribute->resolve_pull_condition($option);

			} else {
				throw new Exception(); // Error
			}
		}

		if(empty($conditions)){
			return null;
		} else if(count($conditions) === 1){
			return $conditions[0];
		} else if($mode === 'OR'){
			return new OrOp(...$conditions);
		} else if($mode === 'AND'){
			return new AndOp(...$conditions);
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


	# Change an attributes value (and check before whether that change is allowed)
	# @param $name: the name of the attribute
	# @param $input: the proposed new value
	# @throws: AttributeValueException[List] if changing the attribute to the proposed value is not allowed
	final public function edit_attribute(string $name, mixed $input) : void {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		if(!$this->has_attribute($name)){
			throw new Exception("Attribute «{$name}» is not defined.");
		}

		if(!$this->$name->is_loaded()){
			throw new AttributeNotLoadedException($this->$name);
		}

		if($this->$name instanceof RelationshipAttribute){
			if(!$this->is_independent()){
				return;
			}
		}

		$this->$name->edit($input);

		if($this->$name->is_dirty() && !$this->$name->is_editable() && !$this->is_new()){
			throw new AttributeNotAlterableException($this->$name);
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
		foreach($this->get_attributes() as $name){
			$this->$name = clone $this->$name;
		}
	}


	function __get($name) {
		# if $this->$name is a defined attribute, return its value
		if($this->has_attribute($name) && $this->$name->is_loaded()){
			return $this->$name->get_value();
		}
	}


	function __isset(string $name) : bool {
		return $this->has_attribute($name) && $this->$name->is_loaded();
	}
}
?>
