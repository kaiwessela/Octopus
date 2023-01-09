<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotLoadedException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use Octopus\Core\Model\Attributes\GeneratedIdentifierAttribute;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Conditions\Annd;
use Octopus\Core\Model\Database\Conditions\Orre;
use Octopus\Core\Model\Database\DatabaseAccess;
use Octopus\Core\Model\Database\Request;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\EntityList;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use Octopus\Core\Model\Relationship;
use ReflectionProperty;

# This trait shares common methods for Entity and Relationship that relate to the loading, altering, validating and
# outputting of attributes.

trait AttributesContaining {

	// UNCOMMENT WHEN 8.2 IS AVAILABLE
	// # Constants to be defined by each object class:
	// protected const DB_TABLE = null; # string, name of the database table that stores the data of the classes entities.
	// protected const PRIMARY_IDENTIFIER = null; # string, property name of the primary identifier attribute.
	// protected const DEFAULT_PULL_ATTRIBUTES = []; # array, same format as for pull($include_attributes).


	# Methods to be implemented by each object class:
	abstract protected static function define_attributes() : array;


	# $attributes and $primary_identifiers are static properties of all classes using this trait (currently Entity and
	# Relationship), so they have the same value across all instances of the respective class.
	private static array $attributes; # stores all the attribute prototypes for each object class
	private static array $primary_identifiers; # stores the primary identifier's name for each object class


	# The context is the object that this entity depends on. This can either be:
	# - null (no object at all), if this entity is independent, meaning it was pulled or created on its own,
	# - another Entity, if this entity is referenced by an EntityReference attribute of the context entity,
	# - an EntityList, if this entity was pulled together with multiple other entities of the same class, or
	# - a Relationship, if this entity is part of a mutual many-to-many relationship with another entity.
	private null|Entity|EntityList|Relationship $context;


	# How does an Entity/EntityList/Relationship connect to the database?
	# The database connection is being established by the controller, wrapped in the DatabaseAccess class and
	# passed on to every object as a reference, so there is only one instance of DatabaseAccess at a time.
	# For independent objects, the DatabaseAccess has to be provided upon construction.
	# Dependent objects use the DatabaseAccess of their context object.
	protected ?DatabaseAccess $db; # see --> DatabaseAccess for how the access mechanism itself works.

	# In order to prevent column name collisions when pulling from multiple tables using joins, every dependent
	# object must have a database prefix set upon construction.
	private ?string $db_prefix;

	private bool $is_new; # Stores whether the entity already exists in the database (true if not).



	# Initialize all attributes from their prototypes. Create and store the prototypes if that have not yet been done.
	final protected function init_attributes() : void {
		if(!isset(self::$attributes)){ # initialize $attributes if it has not yet been
			self::$attributes = [];
		}

		if(!isset(self::$primary_identifiers)){ # initialize $primary_identifiers if it has not yet been
			self::$primary_identifiers = [];
		}

		# load the attribute prototypes and store them in $attributes if they have not been loaded yet
		if(!isset(self::$attributes[static::class])){
			self::$attributes[static::class] = [];

			# array of attributes that have been created by the classes define_attributes() method. they are defined,
			# but not bound or loaded and will be stored as a prototype so that they dont need to be defined again
			# when creating another instance of this class.
			$attribute_prototypes = static::define_attributes();

			$identifiers = []; # array of the property names of all required identifier attributes

			foreach($attribute_prototypes as $name => $prototype){ # loop through the prototypes and do some validation
				if(!$prototype instanceof Attribute){
					throw new Exception("Invalid attribute definition: «{$name}» is not an Attribute.");
				}

				if($prototype->is_bound()){
					throw new Exception("Invalid attribute definition: «{$name}» is already bound to an object.");
				}

				if(!preg_match('/^[a-z0-9_]+$/', $name)){
					throw new Exception("Invalid attribute definition: «{$name}»'s name contains illegal characters.");
				}

				if(!property_exists(static::class, $name)){
					throw new Exception("Invalid attribute definition: no property found for «{$name}».");
				}

				if(property_exists(self::class, $name)){
					throw new Exception("Invalid attribute definition: property «{$name}» is already reserved.");
				}

				$reflection = new ReflectionProperty(static::class, $name);
				if(!$reflection->isProtected()){
					throw new Exception("Invalid attribute definition: property «{$name}» is not protected.");
				}

				self::$attributes[static::class][$name] = $prototype; # store the prototype in $attributes

				# find all required identifier attributes
				if($prototype instanceof IdentifierAttribute && $prototype->is_required()){
					$identifiers[] = $name;
				}
			}

			# try to find the primary identifier attribute for this class
			if(empty($identifiers)){
				throw new Exception('Invalid attribute definitions: no unique identifier has been defined.');
			} else if(!is_null(static::PRIMARY_IDENTIFIER)){ # if PRIMARY_IDENTIFIER is set, check its value
				if(in_array(static::PRIMARY_IDENTIFIER, $identifiers)){
					$primary_identifier = static::PRIMARY_IDENTIFIER;
				} else {
					throw new Exception('Invalid class definition: PRIMARY_IDENTIFIER is not an identifier.');
				}
			} else if(count($identifiers) === 1){ # PRIMARY_IDENTIFIER can be left unset if there is only one identifier
				$primary_identifier = $identifiers[0];
			} else {
				throw new Exception('Invalid class definition: no primary identifier attribute is provided.');
			}

			self::$primary_identifiers[static::class] = $primary_identifier; # store the primary identifier
		}

		# initialize the classes attribute properties by cloning the prototype and binding it to the object
		foreach(self::$attributes[static::class] as $name => $prototype){
			$this->$name = clone $prototype;
			$this->$name->bind($name, $this);
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


	# Returns the DatabaseAccess of this entity or its context, if it is dependent.
	public function &get_db() : DatabaseAccess {
		return $this->db ?? $this->context?->get_db();
	}


	# Return whether the entity is not stored in the database.
	final public function is_new() : bool {
		return $this->is_new;
	}


	# Return whether the entity has been initialized, either by load() or create().
	final public function is_loaded() : bool {
		return isset($this->is_new);
	}


	# Return whether the entity is independent of any other entity, which is the case if it has no context.
	final public function is_independent() : bool {
		return !isset($this->context);
	}


	# Return an array of all attribute names of the object.
	// IMPROVE rename to get_attribute_names
	final public function get_attributes() : array {
		return array_keys(self::$attributes[static::class]);
	}


	# Return whether the entity contains the specified attribute or an attribute with the specified name.
	final public function has_attribute(string|Attribute $attribute) : bool {
		if(is_string($attribute)){
			return in_array($attribute, $this->get_attributes());
		} else {
			return $attribute->parent_is($this);
		}
	}


	# Return the specified attribute of the entity.
	final public function get_attribute(string $name) : Attribute {
		if(in_array($name, $this->get_attributes())){
			return $this->$name;
		} else {
			throw new Exception("Attribute «{$name}» not found.");
		}
	}


	# Return the name of the primary identifier attribute of the entity.
	final public function get_primary_identifier_name() : string {
		return self::$primary_identifiers[static::class];
	}


	# Return the primary identifier attribute of the entity.
	final public function get_primary_identifier() : IdentifierAttribute {
		return $this->get_attribute($this->get_primary_identifier_name());
	}



	# EXPLAINATION: How To Deal With Order
	#
	# 1. get input order chain: [1 => [title, +], 2 => [author:name, +], 3 => [categories:category:name?, +]]
	# 2. split it to get a chain for each select/join request, keep indices:
	# 	articles (main/select): 1 => [title, +],
	# 	authors (forward join): 2 => [name, +],
	# 	categories (reverse join): 
	# 		category (forward join): 3 => [name, +]
	# 3. pass these chains into the request
	#
	# In SelectRequest/JoinRequest, on resolve():
	# 4. merge the own chain and the chains of each forward join below (recursively) together, ordering after the index:
	# 	articles: 1 => [title, +], 2 => [(author.)name, +]
	# 	categories: 3 => [(categories.category.)name, +]
	# 5. reindex the chains:
	# 	articles: 1 => ..., 2 => ...
	# 	categories: 1 => ...
	# 6. append the default/fallback order (which is the main identifier) to the main chain and each backwards join's chain:
	# 	articles: 1 => ..., 2 => ..., 3 => [id, +]
	# 	categories: 1 => ..., 2 => [id, +]
	# 7. glue all chains together:
	# 	1, 2, 3, 4, 5
	
	// NOTE what if there are two reverse joins on the same level? idea: the one with the longer chain takes precedence


	final public function resolve_pull_attributes(Request &$request, array $instructions) : void {
		# $instructions has the following format: [
		#	attribute name => true|false - for pullable attributes only
		#	attribute name => [ - for joinable attributes only
		#		... (same format as $instructions, recursive)
		#	]
		# ]

		# verify the attribute inclusion instructions
		foreach($instructions as $name => $instruction){
			if(!$this->has_attribute($name)){
				throw new Exception("Unknown attribute «{$name}»."); // IMPROVE custom exception
			}

			if(!is_null($instruction) && !is_bool($instruction) && !(is_array($instruction) && $this->$name->is_joinable())){
				throw new Exception("Invalid format of inclusion instruction for attribute «{$name}».");
			}
		}

		foreach($this->get_attributes() as $name){
			# load the inclusion instruction for the attribute or, if that is not set, the default instruction, or null
			$instruction = $instructions[$name] ?? static::DEFAULT_PULL_ATTRIBUTES[$name] ?? null;

			if(is_array($instruction)){ # if the instruction is an array (used for joinable attributes), then:
				$pull = $this->$name->is_pullable(); # pull the attribute if it is also pullable and
				$join = true; # join the attribute.
			} else if($instruction === true || is_null($instruction)){ # if the instruction is true or none on default:
				$pull = true; # pull the attribute and
				$join = false; # do not join the attribute.
			} else if($instruction === false){ # if the instruction is false, neither pull nor join the attribute.
				$pull = false;
				$join = false;
			} else {
				throw new Exception("Invalid format of default inclusion instruction for attribute «{$name}».");
			}

			if($name === $this->get_primary_identifier_name()){ # the primary identifier must always be included
				$pull = true;
			}

			if($pull){ # if the attribute shall be pulled
				if(!$this->$name->is_pullable()){
					throw new Exception("Cannot add non-pullable attribute «{$name}» to the request.");
				}

				$request->add($this->$name); # add the attribute to the request
			}

			if($join){ # if the attribute shall be joined
				if(!$this->$name->is_joinable()){
					throw new Exception("Cannot join non-joinable attribute «{$name}» to the request.");
				}

				// TODO prevent joining context attributes and relationships if the context is an entitylist (?)
				if(!$this->is_independent() && $this->$name->get_class() === $this->context::class){ // TEMP
					continue;
				}

				if(!$this->$name->is_pullable() && !($this->is_independent() || $this->context instanceof EntityList)){ // TEMP
					continue;
				}
				// check until here

				$request->add_join($this->$name->get_join_request($instruction ?? []));
			}
		}
	}


	/*
	# Fill a given SelectRequest/JoinRequest to pull entities of this class with the following information:
	# - which attributes to include in the response
	# - which attributes to order the response columns by
	# @param include_attributes: array of attribute_name => option, where option can be:
	#	- true, to include the attribute,
	#	- false, to omit the attribute,
	#	- for Joinables only: an array, to join the object referenced in the attribute. the array itself can contain
	# 		directives of the same kind for the object's attributes.
	# @param order_by: array of [attribute_name, direction], where direction can be:
	#	- +, ASC, ascending for ascending order
	#	- -, DESC, descending for descending order
	final public function build_pull_request(Request &$request, array $include_attributes, array $order_by) : void {
		$this->process_order_instructions($order_by);

		# verify the attributes that should be included and their options
		foreach($include_attributes as $name => $option){
			if(!$this->has_attribute($name)){
				throw new Exception("Unknown attribute «{$name}»."); // IMPROVE custom exception
			}

			if(!is_null($option) && !is_bool($option) && !(is_array($option) && $this->$name->is_joinable())){
				throw new Exception("Invalid format of inclusion directive for attribute «{$name}».");
			}
		}

		foreach($this->get_attributes() as $name){
			# load the include option for the attribute or, if that does not exist, the default option
			$option = $include_attributes[$name] ?? static::DEFAULT_PULL_ATTRIBUTES[$name] ?? null;

			if(is_array($option)){
				$pull = $this->$name->is_pullable();
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

			if($name === $this->get_primary_identifier_name()){
				$pull = true;
			}

			if($pull){
				if($this->$name->is_pullable()){
					$request->add($this->$name); # add the attribute to the request
				} else {
					throw new Exception("Cannot add non-pullable attribute «{$name}» to the request.");
				}
			} else if($this->$name->has_order_clause()){
				// Warning, order clause but attribute is not included
				throw new Exception("Cannot order results by non-included attribute «{$name}».");
			}

			if($join){
				if($this->$name->is_joinable()){
					// TODO prevent joining context attributes and relationships if the context is an entitylist (?)
					if(!$this->is_independent() && $this->$name->get_class() === $this->context::class){ // TEMP
						continue;
					}
	
					if(!$this->$name->is_pullable() && !($this->is_independent() || $this->context instanceof EntityList)){ // TEMP
						continue;
					}
					// check until here

					$request->add_join($this->$name->get_join_request($option ?? []));
				} else {
					throw new Exception("Cannot join non-joinable attribute «{$name}» to the request.");
				}
			}
		}
	}
	*/


	// NUR SELECTREQUESTS UND REVERSE JOINS BRAUCHEN NE FALLBACK ORDER CLAUSE


	final public function resolve_pull_order(Request &$request, array $instructions) : void {
		foreach($instructions as $index => $order_instruction){
			if(!is_array($order_instruction) || !count($order_instruction) === 2){
				throw new Exception("Invalid order instruction #{$index}.");
			}

			list($compound_attribute_name, $sequence) = $order_instruction;

			if(!is_string($compound_attribute_name)){
				throw new Exception("Invalid attribute format in order instruction #{$index}.");
			}

			$segments = explode('.', $compound_attribute_name);
			$attribute = $segments[0];

			if(!$this->has_attribute($attribute)){
				throw new Exception("Unknown attribute «{$attribute}» in order instruction #{$index}/«{$compound_attribute_name}».");
			}

			$sequence = match($sequence){
				'+', 'ascending', 'asc', 'ASC' => 'ASC',
				'-', 'descending', 'desc', 'DESC' => 'DESC',
				default => throw new Exception("Invalid sequence in attribute instruction #{$original_index}.")
			};

			if(count($segments) === 1){
				$request->order_by($attribute, $sequence, $index);
			} else {
				$request->order_by($segments, $sequence, $index);
			}
		}
	}


	// TODO
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
			return new Orre(...$conditions);
		} else if($mode === 'AND'){
			return new Annd(...$conditions);
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


	# Edit an attributes value (and check before whether that edit is allowed)
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

		if($this->$name instanceof RelationshipsReference){ # RelationshipsReference can only be edited when independent
			if(!$this->is_independent()){
				return;
			}
		}

		$this->$name->edit($input); // TODO why that order?

		if($this->$name->is_dirty() && !$this->$name->is_editable() && !$this->is_new()){
			throw new AttributeNotAlterableException($this->$name);
		}
	}


	# Return the database table in which the entities of this class are stored.
	final public function get_db_table() : string {
		return static::DB_TABLE;
	}


	# Return this entity's database table, prefixed with its database prefix if that is set.
	final public function get_prefixed_db_table() : string {
		if(isset($this->db_prefix)){
			return "{$this->db_prefix}~{$this->get_db_table()}";
		} else {
			return $this->get_db_table();
		}
	}


	// TODO from here
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


	function __isset($name) : bool {
		return $this->has_attribute($name) && $this->$name->is_loaded();
	}
}
?>
