<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Attributes\IDAttribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use PDOException;
use Exception;

# What is a DataObject? // TODO
# A DataObject is basically a representation of a real thing. Any single object that is handled
# by Octopus (i.e. a blog article, a person profile, an image etc.) is handled as an instance of
# this class.
# DataObjects have attributes, in which the actual information is stored. A DataObject that
# represents a person for example has the attributes name, date of birth, etc.
# As there are many different kinds of objects in the real world, it would not make much sense
# to wedge all of them into the same class of DataObject. Therefore, for every different kind of
# object, there is a separate class in Octopus defining specific attributes for it. This class,
# DataObject, is only the superclass for all of them, containing only attributes and methods all
# of these subclasses have in common (like the id and longid attributes).
# DataObjects are stored in a database. To make them handleable for Octopus, it is necessary to
# transfer the data from the database into Octopus and, after editing, back from there into the
# database. This class provides all necessary methods to perform such operations. In this sense,
# it serves as a database abstraction layer, similar to an object-relational mapper.

abstract class Entity {
	protected IDAttribute $id;	# main unique identifier of the object; uneditable; randomly generated on create()
	protected IdentifierAttribute $longid;		# another unique identifier; editable; set by the user

	protected bool $is_new;

	# this class uses the Attributes trait which contains standard methods that handle the attributes of this class
	# for documentation on the following definitions, check the Attributes trait source file
	use Attributes;

	const DB_TABLE = '';
	const DB_PREFIX = ''; # prefix of this object's row names in the database (i.e. [prefix]_id, [prefix]_longid)

	# all child classes must set the following property:
	# protected static array $attributes;

	public readonly null|Entity|EntityList|Relationship $context;
	public readonly ?SelectRequest $pull_request;

	protected ?DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(null|Entity|EntityList|Relationship $context = null, ?DatabaseAccess $db = null) {
		$this->context = &$context;

		if(isset($context)){
			if($context instanceof Entity){
				$this->db =& $db ?? $context->db;
			}

			$this->pull_request = null;
		} else if(!isset($db)){
			throw new Exception('Database access required.');
		}

		if(!isset(static::$attributes)){ # load all attribute definitions
			static::load_attribute_definitions();

			if(!(static::$attributes['id'] ?? null) instanceof IdentifierAttribute){
				throw new Exception('Invalid attribute definitions: valid id definition missing.');
			}
		}

		$this->bind_attributes();

		$this->db = &$db;
	}


	abstract protected static function define_attributes() : array;


	### INITIALIZATION AND LOADING METHODS

	# Initialize a new entity that is not yet stored in the database
	# Generate a random id for the new entity and set all attributes to null
	final public function create() : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$this->is_new = true;

		$this->id->generate(); # generate and set a random, unique id for the entity. (--> IDAttribute)
	}


	# ---> see trait Attributes
	# final protected function bind_attributes() : void;


	# Download entity data from the database and use load() to load it into this Entity object
	# @param $identifier: the identifier string that specifies which entity to download.
	# @param $identify_by: the name of the attribute $identifier is matched with.
	# @param $options: additional, custom pull options
	final public function pull(string $identifier, string $identify_by = 'id', array $options = []) : void {
		if($this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		# verify the identify_by value
		$identifying_attribute = static::$attributes[$identify_by] ?? null;
		if(!$identifying_attribute instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found or is not an identifier.");
		}

		$request = new SelectRequest(static::DB_TABLE);

		foreach(static::$attributes as $name => $attribute){ # $attribute is an AttributeDefinition
			if($attribute instanceof EntityAttribute){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # join Entity attributes recursively
				$request->add_attribute($attribute); // FIXME this is a hotfix. entities need not only be joined, but also pulled. i.e. the key post_image_id must be in the values, not only image_id. that is because load needs this value to check if it is set.
			} else if($attribute instanceof RelationshipAttribute){
				$request->add_join($attribute->get_class()::join(on:static::$attributes['id'])); # join RelationshipList
			} else if($attribute instanceof PropertyAttribute){
				$request->add_attribute($attribute);
			}
		}

		static::shape_select_request($request, $options);

		$request->set_condition(new IdentifierEquals($identifying_attribute, $identifier));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		if($s->rowCount() === 0){
			throw new EmptyResultException($s);
		}

		$this->pull_request = $request; # the pull request might be needed later for count requests in relationships
		$this->load($s->fetchAll());
	}


	# Return a JoinRequest for this entity that can be used by another entity’s or relationship’s pull() method to
	# include this entity as an attribute.
	# single entities that this entity contains as attributes are joined too (recursively), but not relationships!
	# @param $on: The attribute on the calling entity/relationship that identifies this entity
	# paraphrased: LEFT JOIN [this entity’s table] ON [this entity’s prefix].id = [on]
	final public static function join(Attribute $on) : JoinRequest {
		$request = new JoinRequest(static::DB_TABLE, static::get_attribute_definitions()['id'], $on);

		foreach(static::get_attribute_definitions() as $name => $attribute){ # $attribute is an AttributeDefinition
			if($attribute instanceof EntityAttribute){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # recursively join entities this entity contains
			} else if($attribute instanceof PropertyAttribute){
				$request->add_attribute($attribute);
			}
		}

		static::shape_join_request($request);

		return $request;
	}


	protected static function shape_select_request(SelectRequest &$request, array $options) : void {}
	protected static function shape_join_request(JoinRequest &$request) : void {}


	# Load rows of entity data from the database into this Entity object
	# @param $data: single fetched row or multiple rows from the database request’s response
	final public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		# To parse the columns containing our entity data, we must do a distinction:
		if(is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relationships
		} else {
			$row = $data; # without relationships
		}

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof RelationshipAttribute){
				if(isset($this->context)){ # relationships are disabled (thus set null) on non-independent entities
					$this->$name = null;
				} else {
					$class = $attribute->get_class();
					$this->$name = new $class($this); # $this is referenced as context entity
					$this->$name->load($data);
				}

			} else if(array_key_exists($attribute->get_prefixed_db_column(), $row)){
				if($attribute instanceof EntityAttribute){
					$this->$name->load($row);
				} else if($attribute instanceof PropertyAttribute){
					$this->$name->load($row[$attribute->get_prefixed_db_column()]);
				}
			}

		}

		$this->is_new = false;
	}


	### EDITING METHODS

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

		foreach($data as $name => $input){ # loop through all input fields
			if(!isset(static::$attributes[$name])){ # check if the attribute exists
				continue;
			}

			if(!$this->$name->is_loaded()){
				$errors->push(new AttributeNotAlterableException()); // TODO
				continue;
			}

			try {
				$this->edit_attribute($name, $input);
			} catch(AttributeValueException $e){
				$errors->push($e);
			} catch(AttributeValueExceptionList $e){
				$errors->merge($e, $name);
			}
		}

		# if errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	# ---> see trait Attributes
	# final public function edit_attribute(string $name, mixed $input) : void;


	### STORING AND DELETING METHODS

	# Upload this entity’s data into the database.
	# if this entity is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all attributes this entity contains that are Entities or Relationships themselves are pushed too (recursively).
	# @return: true if a database request was performed for this entity, false if not.
	final public function push() : bool {
		if(!$this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		if($this->is_new()){
			$request = new InsertRequest(static::DB_TABLE);
		} else {
			$request = new UpdateRequest(static::DB_TABLE);
			$request->set_condition(new IdentifierEquals(static::$attributes['id'], $this->id->get_value()));
		}

		$errors = new AttributeValueExceptionList();
		$push_values = [];
		$push_later = [];

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof RelationshipAttribute){
				$push_later[] = $name;
			} else if($attribute->is_required() && $this->$name->is_empty()){
				$errors->push(new MissingValueException($attribute));
			} else if($this->is_new() || $this->$name->has_been_edited()){
				$request->add_attribute($attribute);
				$push_values[$attribute->get_db_column()] = $this->$name->get_push_value();
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		$request->set_values($push_values);

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(EmptyRequestException $e){
			return false;
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		foreach($push_later as $name){
			$this->$name?->push();
		}

		return true;
	}


	# Erase this entity out of the database.
	# this does not delete entities it contains as attributes, but all relationships of this entity will be deleted due
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
		$request = new DeleteRequest(static::DB_TABLE);
		$request->set_condition(new IdentifierEquals(static::$attributes['id'], $this->id->get_value()));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		$this->is_new = true;

		return true;
	}


	### OUTPUT METHODS

	# Transform this entity object into an array (containing all its attributes).
	# attributes that are entities themselves are recursively transformed too (using theír own arrayify functions).
	final public function arrayify() : array|null {
		$result = [];

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				$result[$name] = $this->$name->get_value()?->arrayify();
			} else if($attribute instanceof RelationshipAttribute){
				$result[$name] = $this->$name?->arrayify();
			} else {
				$result[$name] = $this->$name->get_value();
			}
		}

		$result = array_merge($result, $this->arrayify_custom()); // TEMP

		return $result;
	}


	protected function arrayify_custom() : array {
		return [];
	}


	### GENERAL METHODS

	public function is_new() : bool {
		return $this->is_new;
	}


	public function is_loaded() : bool {
		return isset($this->is_new);
	}


	public function is_independent() : bool {
		return !isset($this->context);
	}



	// TODO explaination
	final public static function has_relationships() : bool {
		foreach(static::get_attribute_definitions() as $attribute){
			if($attribute instanceof RelationshipAttribute){
				return true;
			}
		}

		return false;
	}


	final public function get_relationships() : ?RelationshipList {
		// TODO check flow state

		foreach(static::get_attribute_definitions() as $name => $attribute){
			if($attribute instanceof RelationshipAttribute){
				return $this->$name;
			}
		}

		return null;
	}
}
?>
