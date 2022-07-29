<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\EntityAndRelationship;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \PDOException;
use \Exception;


abstract class Entity {

	protected bool $is_new;

	# this class uses the Attributes trait which contains standard methods that handle the attributes of this class
	# for documentation on the following definitions, check the Attributes trait source file
	use EntityAndRelationship;
	protected static array $attributes;

	protected const DB_TABLE = null;
	protected const LIST_CLASS = EntityList::class;
	protected const MAIN_IDENTIFIER = null;
	protected string $main_identifier;

	protected null|Entity|EntityList|Relationship $context;
	protected ?string $db_prefix;

	protected ?DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(null|Entity|EntityList|Relationship $context = null, ?DatabaseAccess $db = null, ?string $db_prefix = null) {
		if(is_null($context) === is_null($db)){
			throw new Exception('either one of context or db must be set.');
		}

		$this->context = &$context;

		if($this->is_independent() && !is_null($db_prefix)){
			throw new Exception('independent entities cannot have a database prefix.');
		}

		$this->db_prefix = $db_prefix;
		$this->db = &$db;

		if(!is_string(static::DB_TABLE) || !preg_match('/^[a-z_]+$/', static::DB_TABLE)){
			throw new Exception('invalid db table.');
		}

		if(!is_string(static::LIST_CLASS) || !(static::LIST_CLASS === EntityList::class || is_subclass_of(static::LIST_CLASS, EntityList::class))){
			throw new Exception('invalid list class.');
		}

		$this->load_attributes();

		foreach($this->get_attributes() as $name){
			// the order of these two ifs is counterintuitive, but it is actually better for the performance this way
			if($name !== (static::MAIN_IDENTIFIER ?? $name)){
				continue;
			}

			if($this->$name instanceof IdentifierAttribute && $this->$name->is_required()){
				$this->main_identifier = $name;
				break;
			}
		}

		if(!isset($this->main_identifier)){
			throw new Exception('Invalid attribute definitions: main unique identifier missing/not found.');
		}
	}


	public function &get_db() : DatabaseAccess { // TODO check
		return $this->db ?? $this->context?->get_db();
	}


	abstract protected static function define_attributes() : array;


	### INITIALIZATION AND LOADING METHODS

	# ---> see trait Attributes
	# final protected function bind_attributes() : void;


	# Download entity data from the database and use load() to load it into this Entity object
	# @param $identifier: the identifier string that specifies which entity to download.
	# @param $identify_by: the name of the attribute $identifier is matched with.
	# @param $options: additional, custom pull options
	final public function pull(string $identifier, string $identify_by = 'id', array $attributes = []) : void {
		if($this->is_loaded() || !$this->is_independent()){
			throw new CallOutOfOrderException();
		}

		# verify the identify_by value
		if(!$this->has_attribute($identify_by)){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found.");
		} else if(!$this->$identify_by instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» is not an identifier.");
		}

		$request = new SelectRequest($this);
		$this->build_pull_request($request, $attributes);
		$request->set_condition(new IdentifierEquals($this->$identify_by, $identifier));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		if($s->rowCount() === 0){
			throw new EmptyResultException($s);
		}

		$this->load($s->fetchAll());
	}


	final public function join(Attribute $on, string $identify_by, array $attributes = []) : JoinRequest {
		if(!$this->has_attribute($identify_by)){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found.");
		} else if(!$this->$identify_by instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» is not an identifier.");
		}

		$request = new JoinRequest($this, $this->$identify_by, $on);
		$this->build_pull_request($request, $attributes);
		return $request;
	}


	# Load rows of entity data from the database into this Entity object
	# @param $data: single fetched row or multiple rows from the database request’s response
	final public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		# To parse the columns containing our entity data, we must do a distinction:
		if(isset($data[0]) && is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relationships
		} else {
			$row = $data; # without relationships
		}

		foreach($this->get_attributes() as $name){
			if($this->$name->is_pullable() && !array_key_exists($this->$name->get_result_column(), $row)){
				continue;
			} else if($this->$name->is_joinable() && !array_key_exists($this->$name->get_detection_column(), $row)){
				continue;
			}

			if($this->$name instanceof PropertyAttribute){
				$this->$name->load($row[$this->$name->get_result_column()]);
			} else if($this->$name instanceof EntityAttribute){
				$this->$name->load($row);
			} else if($this->$name instanceof RelationshipAttribute){
				$this->$name->load($data, is_complete:$this->is_independent()); // the relationshiplist is complete if this is independent because then there definitely was no limit in the request. is_complete determines whether the relationships can be edited.
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
			if(!$this->has_attribute($name)){ # check if the attribute exists
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
			$request = new InsertRequest($this);
		} else {
			$request = new UpdateRequest($this);
			$request->set_condition(new IdentifierEquals($this->get_main_identifier_attribute(), $this->get_main_identifier_attribute()->get_value()));
		}

		$errors = new AttributeValueExceptionList();

		foreach($this->get_attributes() as $name){
			if($this->$name->is_pullable()){
				if($this->$name->is_required() && $this->$name->is_empty()){
					$errors->push(new MissingValueException($this->$name));
				} else if($this->$name->is_dirty()){
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
		} catch(EmptyRequestException $e){
			$request_performed = false;
		}

		foreach($this->get_attributes() as $name){
			if($this->$name instanceof RelationshipAttribute){
				$request_performed |= $this->$name->push();
			} else if($this->$name->is_pullable()){
				$this->$name->set_clean();
			}
		}

		return $request_performed;
	}


	# Delete this entity out of the database.
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
		$request = new DeleteRequest($this);
		$request->set_condition(new IdentifierEquals($this->get_main_identifier_attribute(), $this->get_main_identifier_attribute()->get_value()));

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


	final public static function create_list(DatabaseAccess $db) : EntityList {
		// TODO check LIST_CLASS
		$class = static::LIST_CLASS;
		return new $class($db, static::class);
	}
}
?>
