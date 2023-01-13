<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\AttributesContaining;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotLoadedException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use Octopus\Core\Model\Attributes\GeneratedIdentifierAttribute;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Conditions\Annd;
use Octopus\Core\Model\Database\Conditions\IdentifierEquals;
use Octopus\Core\Model\Database\Conditions\Orre;
use Octopus\Core\Model\Database\DatabaseAccess;
use Octopus\Core\Model\Database\Exceptions\DatabaseException;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use Octopus\Core\Model\Database\Request;
use Octopus\Core\Model\Database\Requests\DeleteRequest;
use Octopus\Core\Model\Database\Requests\InsertRequest;
use Octopus\Core\Model\Database\Requests\Join;
use Octopus\Core\Model\Database\Requests\SelectRequest;
use Octopus\Core\Model\Database\Requests\UpdateRequest;
use Octopus\Core\Model\EntityList;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use Octopus\Core\Model\Relationship;
use PDOException;
use ReflectionProperty;


abstract class Entity {

	# Constants to be defined by each child class:
	protected const LIST_CLASS = EntityList::class;

	
	# Properties to be set by each entity class:
	# For each attribute, a property has to be defined in the following form:
	# protected [child of Attribute] $[name];
	# ...


	protected const DB_TABLE = null;
	protected const PRIMARY_IDENTIFIER = null;
	protected const DEFAULT_PULL_ATTRIBUTES = [];


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
	protected null|Entity|EntityList|Relationship $context;


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



	# Initialize a newly created instance of the entity.
	final function __construct(null|Entity|EntityList|Relationship $context = null, DatabaseAccess $db = null, ?string $db_prefix = null) {
		$this->context = &$context;

		if($this->is_independent()){
			if(!isset($db)){
				throw new Exception('Invalid entity construction: db is required on independent entities.');
			}

			if(isset($db_prefix)){
				throw new Exception('Invalid entity construction: db_prefix cannot be set on independent entities.');
			}
		} else {
			if(isset($db)){
				throw new Exception('Invalid entity construction: db cannot be set on dependent entities.');
			}

			if(!isset($db_prefix) && !($this->context instanceof EntityList)){
				throw new Exception('Invalid entity construction: db_prefix is required on independent non-list entities.');
			}
		}

		$this->db_prefix = $db_prefix;
		$this->db = &$db;

		# check that the DB_TABLE constant is formally valid
		if(!is_string(static::DB_TABLE) || !preg_match('/^[a-z_]+$/', static::DB_TABLE)){
			throw new Exception('invalid db table.');
		}

		# check that the LIST_CLASS constant is actually a valid EntityList class
		if(!is_string(static::LIST_CLASS) || !(static::LIST_CLASS === EntityList::class || is_subclass_of(static::LIST_CLASS, EntityList::class))){
			throw new Exception('invalid list class.');
		}

		$this->init_attributes(); # initialize the attributes

		$this->init(); # call the custom initialization method
	}


	# Custom initialization method that is called at the end of __construct() and can be defined by child classes.
	protected function init() : void {}


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
	// TODO until here


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


	# Download entity data from the database and use load() to load it into this Entity object
	# @param $identifier: the identifier string that specifies which entity to download.
	# @param $identify_by: the name of the attribute $identifier is matched with.
	# @param $include_attributes: which attributes to include in the result. Array of attribute => rule.
	# 	rule = true to include, false to omit, Array to join (for Joinables, nestable).
	# @param $order_by: the attributes to sort the result by. Array of [attribute, direction ('ASC', 'DESC')].
	final public function pull(string $identifier, ?string $identify_by = null, array $include_attributes = [], array $order_by = []) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		# verify the identify_by value
		if(is_null($identify_by)){ # if $identify_by is not set, assume the main identifier attribute
			$identify_by = $this->get_primary_identifier_name();
		} else if(!$this->has_attribute($identify_by)){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found.");
		} else if(!$this->$identify_by instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» is not an identifier.");
		}

		$request = new SelectRequest($this);
		$this->resolve_pull_attributes($request, $include_attributes);
		$this->resolve_pull_order($request, $order_by);
		$request->where(new IdentifierEquals($this->$identify_by, $identifier));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		if($s->rowCount() === 0){ # if the result is empty, no entity with this identifier was found.
			throw new EmptyResultException($s);
		}

		$this->load($s->fetchAll()); # load the received data into the attributes
	}


	# Create a Join to pull these entities together with their context entity of another class.
	# @param $on: The attribute of the context entity that stores the reference to this entity.
	# @param $identify_by: The attribute of this entity by which this entity is identified.
	# @param $include_attributes and $order_by: see pull().
	// IMPROVE Attribute -> EntityReference?
	final public function join(Attribute $on, string $identify_by, array $include_attributes) : Join {
		# verify the identify_by attribute
		if(!$this->has_attribute($identify_by)){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found.");
		} else if(!$this->$identify_by instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» is not an identifier.");
		}

		$request = new Join($this, $this->$identify_by, $on);
		$this->resolve_pull_attributes($request, $include_attributes);
		return $request;
	}


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

				$request->include($this->$name); # add the attribute to the request
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

				$request->join($this->$name->get_join_request($instruction ?? []));
			}
		}
	}


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


	# Load rows of entity data from the database into this entity's attributes.
	# @param $data: single fetched row or multiple rows from the database request’s response
	final public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		# To parse the columns containing our entity data, we must distinguish:
		if(isset($data[0]) && is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relationships
		} else {
			$row = $data; # without relationships
		}

		foreach($this->get_attributes() as $name){
			# attributes should only be loaded if they are included in the result, so check that first
			# if the attribute is pullable, check whether it has a column in the result
			# if the attribute is not pullable, check whether it has a detection column in the result
			if($this->$name->is_pullable()){
				if(!array_key_exists($this->$name->get_result_column(), $row)){
					continue;
				}
			} else if(!array_key_exists($this->$name->get_detection_column(), $row)){
				continue;
			}

			if($this->$name instanceof PropertyAttribute){
				$this->$name->load($row[$this->$name->get_result_column()]);
			} else if($this->$name instanceof EntityReference){
				$this->$name->load($row);
			} else if($this->$name instanceof RelationshipsReference){
				// TODO
				$this->$name->load($data, is_complete:$this->is_independent()); // the relationshiplist is complete if this is independent because then there definitely was no limit in the request. is_complete determines whether the relationships can be edited.
			}
		}

		$this->is_new = false;
	}



	# Edit multiple attributes at once, for example to process POST data from an html form
	# @param $data: an array of all new values, with the attribute name being the key:
	# 	[attribute_name => new_attribute_value, ...]
	#	attributes that are not contained are ignored
	# @throws: AttributeValueExceptionList
	final public function receive_input(array $data) : void {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		# create a new container exception that buffers and stores all AttributeValueExceptions
		# that occur during the editing of the attributes (i.e. invalid or missing values)
		$errors = new AttributeValueExceptionList();

		// FIXME file inputs via $_FILES are not taken into account. the following is a hotfix.
		$data = array_merge($data, array_flip(array_keys($_FILES)));

		foreach($data as $name => $input){
			try {
				$this->edit_attribute($name, $input);
			} catch(AttributeValueException $e){
				$errors->push($e);
			} catch(AttributeValueExceptionList $e){
				$errors->merge($e, $name);
			}
		}

		# if any errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}
	}


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



	# Upload this entity’s data into the database.
	# if this entity is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all attributes this entity contains that are Entities or Relationships themselves are pushed too (recursively).
	# @return: true if a database request was performed as a result of this process, false if not.
	final public function push() : bool {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		if($this->is_new()){
			$request = new InsertRequest($this);
		} else {
			$request = new UpdateRequest($this);
			$request->where(new IdentifierEquals($this->get_primary_identifier(), $this->get_primary_identifier()->get_value()));
		}

		$errors = new AttributeValueExceptionList();

		foreach($this->get_attributes() as $name){
			if($this->$name->is_pullable()){ # only pullable attributes can be updated this way 
				if($this->$name->is_required() && $this->$name->is_empty()){ # if a required attribute has not been set
					$errors->push(new MissingValueException($this->$name));
				} else if($this->$name->is_dirty()){ # if the attribute value was edited, add it to the request
					$request->include($this->$name);
				}
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
			$request_performed = true;
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		} catch(EmptyRequestException $e){ # if no attribute values have been edited, the request will not be performed
			if($this->is_new()){
				throw $e;
			} else {
				$request_performed = false;
			}
		}

		foreach($this->get_attributes() as $name){
			if($this->$name instanceof RelationshipsReference){ # push all RelationshipsReferences
				$request_performed |= $this->$name->push();
			} else if($this->$name->is_pullable()){ # set all pullable entities to be in sync with the database
				$this->$name->set_clean();
			}
		}

		return $request_performed;
	}


	# Delete this entity out of the database.
	# This does not delete entities it contains as attributes, but all relationships of this entity will be deleted due
	# to the mysql ON DELETE CASCADE constraint.
	# @return: true if a database request was performed, false if not (i.e. because the entity still/already is local)
	final public function delete() : bool {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		if($this->is_new()){
			# this entity is not yet or not anymore stored in the database, so just return false
			return false;
		}

		# create a DeleteRequest and set the WHERE condition to id = $this->id
		$request = new DeleteRequest($this);
		$request->where(new IdentifierEquals($this->get_primary_identifier(), $this->get_primary_identifier()->get_value()));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		$this->is_new = true; # set this entity to new as it is no longer stored in the database

		return true;
	}



	# Transform this entity object into an array (containing all its attributes).
	# attributes that are entities themselves are recursively transformed too (using theír own arrayify functions).
	final public function arrayify() : array|null {
		$result = [];

		foreach($this->get_attributes() as $name){
			if($this->$name->is_loaded()){
				$result[$name] = $this->$name->arrayify();
			}
		}

		$result = array_merge($result, $this->arrayify_custom()); // TEMP

		return $result;
	}


	protected function arrayify_custom() : array {
		return [];
	}


	# Return an instance of this entity's list class.
	final public static function create_list(DatabaseAccess $db) : EntityList {
		// TODO check LIST_CLASS
		$class = static::LIST_CLASS;
		return new $class($db, static::class);
	}
}
?>
