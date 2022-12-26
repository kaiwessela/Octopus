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
use Octopus\Core\Model\Database\Conditions\IdentifierEquals;
use Octopus\Core\Model\Database\DatabaseAccess;
use Octopus\Core\Model\Database\Exceptions\DatabaseException;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Octopus\Core\Model\Database\Requests\DeleteRequest;
use Octopus\Core\Model\Database\Requests\InsertRequest;
use Octopus\Core\Model\Database\Requests\JoinRequest;
use Octopus\Core\Model\Database\Requests\UpdateRequest;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use PDOException;


abstract class Relationship {
	# protected ID $id;
	# protected EntityReference $[name of 1st entity];
	# protected EntityReference $[name of 2nd entity];
	# ...other attributes

	protected string $context_attribute;
	protected string $relatum_attribute;

	protected Entity $context;

	const DISTINCT = false;

	protected bool $is_new;

	use AttributesContaining;
	// protected static array $attributes;

	protected const DB_TABLE = '';

	protected string $db_prefix;


	### CONSTRUCTION METHODS


	final function __construct(Entity $context, string $db_prefix) {
		$this->db_prefix = $db_prefix;

		$this->init_attributes();

		foreach($this->get_attributes() as $name){
			if($this->$name instanceof IdentifierAttribute){
				if($this->$name->is_required()){
					$this->main_identifier = $name;
				}
			} else if($this->$name instanceof PropertyAttribute){
				continue;
			} else if($this->$name instanceof EntityReference){
				if($this->$name->get_class() === $context::class){
					if(isset($this->context_attribute)){
						throw new Exception("Context collision: There can only be one context attribute.");
					}

					$this->$name->load($context);
					$this->context_attribute = $name;
					$this->context = &$context;
				} else {
					if(isset($this->relatum_attribute)){
						throw new Exception("Relatum collision: There can only be one relatum attribute.");
					}

					$this->relatum_attribute = $name;
				}
			} else {
				throw new Exception("Invalid attribute defined: «{$name}».");
			}
		}

		if(!isset($this->main_identifier)){
			throw new Exception('Invalid attribute definitions: main unique identifier missing/not found.');
		}

		if(!isset($this->context_attribute) || !isset($this->relatum_attribute)){
			throw new Exception('Attribute error.'); // TODO
		}
	}


	public function &get_db() : DatabaseAccess { // TODO check
		return $this->context->get_value()->get_db();
	}


	abstract protected static function define_attributes() : array;


	final public function join(array $include_attributes, array $order_by) : JoinRequest {
		// TEMP the last argument uses a hotfix
		// $request = new JoinRequest($this, $this->get_context_attribute(), $this->context->get_attribute($this->get_context_attribute()->get_identify_by()));
		$request = new JoinRequest($this, $this->get_context_attribute(), $this->context->get_main_identifier_attribute());
		$this->build_pull_request($request, $include_attributes, $order_by);
		return $request;
	}


	### INITIALIZATION AND LOADING METHODS

	# ---> see trait Attributes
	# final protected function bind_attributes() : void;


	# Load rows of relationship and entity data from the database into this Relationship object
	# @param $data: single fetched row or multiple rows from the database request's response
	final public function load(array $row) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		foreach($this->get_attributes() as $name){
			if($this->$name instanceof PropertyAttribute){
				if(!array_key_exists($this->$name->get_result_column(), $row)){
					continue;
				}

				$this->$name->load($row[$this->$name->get_result_column()]);
			} else if($this->$name instanceof EntityReference){
				if(!array_key_exists($this->$name->get_detection_column(), $row)){
					continue;
				}

				$this->$name->load($row);
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
			if(!$this->has_attribute($name)){
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
			$request = new InsertRequest($this);
		} else {
			$request = new UpdateRequest($this);
			$request->set_condition(new IdentifierEquals($this->get_main_identifier_attribute(), $this->get_main_identifier_attribute()->get_value()));
		}

		$errors = new AttributeValueExceptionList();
		$push_values = [];

		foreach($this->get_attributes() as $name => $attribute){
			if($attribute instanceof EntityReference && !$this->is_new()){
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
			$s = $this->get_db()->prepare($request->get_query());
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
		$request = new DeleteRequest($this);
		$request->set_condition(new IdentifierEquals($this->get_main_identifier_attribute(), $this->get_main_identifier_attribute()->get_value()));

		try {
			$s = $this->get_db()->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		$this->is_new = true;

		return true;
	}


	### OUTPUT METHODS

	# Return the arrayified joined entity
	final public function arrayify() : ?array {
		return $this->get_relatum()->arrayify();
	}


	### GENERAL METHODS


	protected function get_context_attribute() : EntityReference {
		return $this->{$this->context_attribute};
	}


	protected function get_relatum_attribute() : EntityReference {
		return $this->{$this->relatum_attribute};
	}


	public function get_relatum() : Entity {
		return $this->get_relatum_attribute()->get_value();
	}


	protected const DEFAULT_PULL_ATTRIBUTES = [];
}
?>
