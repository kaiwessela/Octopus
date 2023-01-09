<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\AttributesContaining;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Core\Model\Database\Conditions\IdentifierEquals;
use Octopus\Core\Model\Database\DatabaseAccess;
use Octopus\Core\Model\Database\Exceptions\DatabaseException;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use Octopus\Core\Model\Database\Requests\DeleteRequest;
use Octopus\Core\Model\Database\Requests\InsertRequest;
use Octopus\Core\Model\Database\Requests\JoinRequest;
use Octopus\Core\Model\Database\Requests\SelectRequest;
use Octopus\Core\Model\Database\Requests\UpdateRequest;
use Octopus\Core\Model\EntityList;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use Octopus\Core\Model\Relationship;
use PDOException;


abstract class Entity {

	# this class uses the AttributesContaining trait to share methods with Relationship.
	use AttributesContaining;


	# Constants to be defined by each child class:
	protected const LIST_CLASS = EntityList::class;

	
	# Properties to be set by each entity class:
	# For each attribute, a property has to be defined in the following form:
	# protected [child of Attribute] $[name];
	# ...


	// REMOVE WHEN 8.2 IS AVAILABLE
	protected const DB_TABLE = null;
	protected const PRIMARY_IDENTIFIER = null;
	protected const DEFAULT_PULL_ATTRIBUTES = [];



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
		$request->set_condition(new IdentifierEquals($this->$identify_by, $identifier));

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


	# Create a JoinRequest to pull these entities together with their context entity of another class.
	# @param $on: The attribute of the context entity that stores the reference to this entity.
	# @param $identify_by: The attribute of this entity by which this entity is identified.
	# @param $include_attributes and $order_by: see pull().
	// IMPROVE Attribute -> EntityReference?
	final public function join(Attribute $on, string $identify_by, array $include_attributes) : JoinRequest {
		# verify the identify_by attribute
		if(!$this->has_attribute($identify_by)){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found.");
		} else if(!$this->$identify_by instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» is not an identifier.");
		}

		$request = new JoinRequest($this, $this->$identify_by, $on);
		$this->resolve_pull_attributes($request, $include_attributes);
		return $request;
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
			$request->set_condition(new IdentifierEquals($this->get_primary_identifier(), $this->get_primary_identifier()->get_value()));
		}

		$errors = new AttributeValueExceptionList();

		foreach($this->get_attributes() as $name){
			if($this->$name->is_pullable()){ # only pullable attributes can be updated this way 
				if($this->$name->is_required() && $this->$name->is_empty()){ # if a required attribute has not been set
					$errors->push(new MissingValueException($this->$name));
				} else if($this->$name->is_dirty()){ # if the attribute value was edited, add it to the request
					$request->add($this->$name);
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
		$request->set_condition(new IdentifierEquals($this->get_primary_identifier(), $this->get_primary_identifier()->get_value()));

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
