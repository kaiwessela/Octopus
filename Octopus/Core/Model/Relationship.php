<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Attributes\Attributes;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\IDAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Attributes\Exceptions\MissingValueException;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use \Octopus\Core\Model\Entity;
use \PDOException;
use \Exception;


abstract class Relationship {
	protected IDAttribute $id;
	# protected EntityAttribute $[name of 1st entity];
	# protected EntityAttribute $[name of 2nd entity];
	# ...other attributes

	protected EntityAttribute $context;
	protected EntityAttribute $relatum;

	const DISTINCT = false;

	protected bool $is_new;

	use Attributes;

	protected DatabaseAccess $db;

	# all child classes must set the following property:
	# protected static array $attributes;



	// HIER weiter: DB-Übergabe in allen Klassen überprüfen und konsistent machen




	### CONSTRUCTION METHODS

	final function __construct(Entity $context, DatabaseAccess $db) {
		if(!isset(static::$attributes)){
			static::load_attribute_definitions();

			// TODO validate attribute definitions (type, required, editable)
		}

		$this->db = &$db;

		$this->bind_attributes();

		foreach(static::$attributes as $name => $attribute){
			if(!$attribute instanceof EntityAttribute){
				continue;
			}

			if($attribute->get_class() === $context::class){
				$this->$name->load($context); // IDEA
				$this->context = &$this->$name;
			} else {
				$this->relatum = &$this->$name;
			}
		}

		// TODO check that all entity attributes have been handled properly
	}

	abstract protected static function define_attributes() : array;


	### INITIALIZATION AND LOADING METHODS

	# Initialize a new relationship that is not yet stored in the database
	# Generate a random id for the new relationship and set all attributes to null
	final public function create() : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$this->is_new = true;

		$this->id->generate(); # generate and set a random, unique id for the relationship. (--> IDAttribute)
	}


	# ---> see trait Attributes
	# final protected function bind_attributes() : void;


	# Load rows of relationship and entity data from the database into this Relationship object
	# @param $data: single fetched row or multiple rows from the database request's response
	final public function load(array $data, ?Entity $relatum = null) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				if($attribute->get_class() === $this->context->get_class()){
					continue;
				}

				if(!array_key_exists($attribute->get_prefixed_db_column(), $data)){
					// an error occured; invalid data
				}

				$this->relatum->load($relatum ?? $data);
			} else if($attribute instanceof PropertyAttribute){
				$this->$name->load($data[$attribute->get_prefixed_db_column()]);
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
	final public function receive_input(array $input) : void {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		# create a new container exception that buffers and stores all AttributeValueExceptions
		# that occur during the editing of the attribute (i.e. invalid or missing inputs)
		$errors = new AttributeValueExceptionList();

		// FIXME file inputs via $_FILES are not taken into account. the following is a hotfix.
		$data = array_merge($data, array_flip(array_keys($_FILES)));

		foreach($data as $name => $input){ # loop through all input fields
			if(!isset(static::$attributes[$name])){
				continue;
			}

			if(!$this->$name->is_loaded()){ // TODO really necessary?
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

	# Upload this relationship's data into the database.
	# if this relationship is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all attributes this relationship contains that are Entities themselves are pushed too (recursively).
	# @return: true if a database request was performed for this entity, false if not.
	final public function push() : bool {
		if(!$this->is_loaded()){
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

		foreach(static::$attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute && !$this->is_new()){
				continue;
			}

			if($attribute->is_required() && $this->$name->is_empty()){
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

		return true;
	}


	# Erase this relationship out of the database.
	# this does not delete its context and realtum entities.
	# @return: true if a database request was performed, false if not (i.e. because $this is still/already local)
	final public function delete() : bool {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		if($this->is_new()){
			# this relationship is not yet or not anymore stored in the database, so just return false
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

	# Return the arrayified joined entity
	final public function arrayify() : array {
		return $this->relatum->get_value();
	}


	### GENERAL METHODS

	public function is_new() : bool {
		return $this->is_new;
	}


	public function is_loaded() : bool {
		return isset($this->is_new);
	}


	public function &get_relatum() : Entity {
		return $this->relatum;
	}
}
?>
