<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\Attributes;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\Collection;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \Octopus\Core\Model\FlowControl\Flow;
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
	protected array $attributes;
	protected bool $is_new;

	const DB_TABLE = '';
	const DB_PREFIX = ''; # prefix of this object's row names in the database (i.e. [prefix]_id, [prefix]_longid)

	public readonly null|Entity|EntityList|Relationship $context;
	public readonly ?SelectRequest $pull_request;

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Flow $flow; # this class uses the Flow class to control the order of its method calls. see there for more.



	### CONSTRUCTION METHODS

	final abstract public static function get_attribute_definitions() : array {
		$attributes = static::define_attributes();

		if(!isset($attributes['id'])){
			throw new Exception('Invalid attribute definitions: id attribute missing.');
		}

		foreach($attributes as $name => $attribute){
			if(!$attribute instanceof Attribute){ # check whether the defined attribute is actually an Attribute
				throw new Exception("Invalid attribute definition: «{$name}».");
			}

			# the id attribute must both be an IDAttribute and be the only IDAttribute
			if(($name === 'id') !== ($attribute instanceof IDAttribute)){
				throw new Exception('Invalid attribute definition: id attribute invalid.');
			}

			$attributes[$name]->init($this::class, $name);
		}

		return $attributes;
	}


	final function __construct(null|Entity|EntityList|Relationship &$context = null) {
		$this->context = &$context;

		if(!is_null($context)){
			$this->pull_request = null;
		}

		$this->attributes = static::get_attribute_definitions();

		foreach($this->attributes as $name => $_){
			$this->attributes[$name]->bind($this);
		}

		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->flow = new Flow([
			['root', 'constructed'], 	# flow entry edge
			['constructed', 'created'], # create()
			['constructed', 'loaded'], 	# pull(), load()
			['created', 'edited'], 		# receive_input(), edit_attribute()
			['created', 'freezing'], 	# after editing fails, e.g. to inspect the entity
			['loaded', 'edited'], 		# receive_input(), edit_attribute()
			['loaded', 'deleted'], 		# delete()
			['loaded', 'freezing'], 	# freeze(), arrayify()
			['loaded', 'storing'], 		# no impact because entity has not yet been altered
			['edited', 'edited'], 		# editing can be done multiple times in sequence
			['edited', 'storing'], 		# push()
			['edited', 'deleted'], 		# nonsense; changes are not being saved
			['edited', 'freezing'], 	# changes are not being saved, but helpful if editing fails
			['storing', 'stored'], 		# two-step storing process
			['storing', 'freezing'], 	# helpful if storing fails
			['stored', 'freezing'], 	# freeze(), arrayify()
			['stored', 'storing'], 		# no impact, but allowed
			['stored', 'edited'], 		# entity can be edited again after storing
			['deleted', 'freezing'], 	# entity can still be output after deleting
			['deleted', 'deleted'], 	# no impact, but allowed
			['deleted', 'editing'], 	# deleted entities can be edited (and stored again after that)
			['deleted', 'storing'], 	# deleted entities can be re-stored
			['freezing', 'frozen'], 	# two-step freezing process; end
			['frozen', 'freezing']
		]);

		$this->flow->start();
	}


	### INITIALIZATION AND LOADING METHODS

	# Initialize a new entity that is not yet stored in the database
	# Generate a random id for the new entity and set all attributes to null
	final public function create() : void {
		$this->flow->step('created');
		$this->is_new = true;

		$this->attributes['id']->generate(); # generate and set a random, unique id for the entity.
	}


	# Download entity data from the database and use load() to load it into this Entity object
	# @param $identifier: the identifier string that specifies which entity to download.
	# @param $identify_by: the name of the attribute $identifier is matched with.
	# @param $options: additional, custom pull options
	final public function pull(string $identifier, string $identify_by = 'id', array $options = []) : void {
		$this->flow->check_step('loaded');

		# verify the identify_by value
		if(!isset($this->attributes[$identify_by]) || !$this->attributes[$identify_by] instanceof IdentifierAttribute){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found or is not an identifier.");
		}

		$request = new SelectRequest(static::DB_TABLE);

		foreach($this->attributes as $attribute){
			if($attribute instanceof EntityAttribute){
				$request->
				$request->join($attribute);
			} else if($attribute instanceof RelationshipListAttribute){

			} else {

			}

			if($attribute->is_pullable()){
				$request->add_attribute($attribute);
			}

			if($attribute->is_joinable()){
				$request->add_join($attribute->join());
			}
		}

		static::shape_select_request($request, $options);

		$request->set_condition(new IdentifierEquals($this->attributes[$identify_by], $identifier));

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

		foreach(static::get_attribute_definitions() as $name => $attribute){
			if($attribute->is_pullable()){
				$request->add_attribute($attribute);

				if($attribute->is_joinable()){
					$request->add_join(); // TODO
				}
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
		$this->flow->check_step('loaded');

		# To parse the columns containing our entity data, we must do a distinction:
		if(is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relationships
		} else {
			$row = $data; # without relationships
		}

		# loop through all attributes and load the value
		foreach($this->attributes as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				$this->attributes[$name]->load($row);
			} else if($attribute instanceof RelationshipListAttribute){
				$this->attributes[$name]->load($data);
			} else {
				$this->attributes[$name]->load($row[$attribute->get_prefixed_db_column()]);
			}
		}

		$this->flow->step('loaded');
		$this->is_new = true;
	}


	### EDITING METHODS

	# Edit multiple attributes at once, for example to process POST data from an html form
	# @param $data: an array of all new values, with the attribute name being the key:
	# 	[attribute_name => new_attribute_value, ...]
	#	attributes that are not contained are ignored
	# @throws: AttributeValueExceptionList
	final public function receive_input(array $data) : void {
		$this->flow->check_step('edited');

		# create a new container exception that buffers and stores all AttributeValueExceptions
		# that occur during the editing of the attributes (i.e. invalid or missing values)
		$errors = new AttributeValueExceptionList();

		// FIXME file inputs via $_FILES are not taken into account. the following is a hotfix.
		$data = array_merge($data, array_flip(array_keys($_FILES)));

		foreach($data as $name => $input){ # loop through all input fields
			if(!isset($this->attributes[$name])){ # check if the attribute exists
				continue;
			}

			try {
				// TODO check editability

				$this->attributes[$name]->edit($input);
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


	final public function edit_attribute($name, $input) : void {

	}


	### STORING AND DELETING METHODS

	# Upload this entity’s data into the database.
	# if this entity is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all attributes this entity contains that are Entities or Relationships themselves are pushed too (recursively).
	# @return: true if a database request was performed for this entity, false if not.
	final public function push() : bool {
		# if $this is already at storing right now, do nothing. this prevents endless loops
		if($this->flow->is_at('storing')){
			return false;
		}

		$this->flow->step('storing'); # start the storing process

		if($this->is_new()){
			# this entity is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest(static::DB_TABLE);
		} else {
			$request = new UpdateRequest(static::DB_TABLE);
			$request->set_condition(new IdentifierEquals($this->attributes['id'], $this->attributes['id']->get_value()));
		}

		foreach($this->attributes as $name => $attribute){
			if($attribute->is_pullable() && $attribute->has_been_edited()){
				$request->add_attribute($attribute);
			}
		}

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(EmptyRequestException $e){
			return false;
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}
		// IDEA maybe use finally to cleanup attributes on fail or store them on success

		foreach($this->attributes as $name => $_){
			$this->attributes[$name]->store();
		}

		$this->flow->step('stored'); # finish the storing process

		return true; # return whether a request was performed (for this entity only)
	}


	# Erase this entity out of the database.
	# this does not delete entities it contains as attributes, but all relationships of this entity will be deleted due
	# to the mysql ON DELETE CASCADE constraint.
	# @return: true if a database request was performed, false if not (i.e. because the entity still/already is local)
	final public function delete() : bool {
		$this->flow->check_step('deleted');

		if($this->is_new()){
			# this entity is not yet or not anymore stored in the database, so just return false
			$this->flow->step('deleted');
			return false;
		}

		# create a DeleteRequest and set the WHERE condition to id = $this->id
		$request = new DeleteRequest(static::DB_TABLE);
		$request->set_condition(new IdentifierEquals($this->attributes['id'], $this->attributes['id']->get_value()));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		foreach($this->attributes as $attribute){
			$attribute->erase();
		}

		$this->flow->step('deleted');
		$this->is_new = true;

		return true;
	}


	### OUTPUT METHODS

	# ---> see trait Attributes
	# final public function freeze() : void;


	# Transform this entity object into an array (containing all its attributes).
	# attributes that are entities themselves are recursively transformed too (using theír own arrayify functions).
	final public function export() : array|null {
		# if this entity is already in the freezing process, return null. prevents endless loops
		# this also makes sure that this entity only occurs once in the final array, preventing redundancies
		if($this->flow->is_at('freezing')){
			return null;
		}

		$this->flow->step('freezing'); # start the freezing process

		$this->db->disable(); # disable the database access

		$result = [];

		# loop through all attributes and copy them to $result. for entities, copy their arrayified version
		foreach($this->attributes as $name => $attribute){
			$result[$name] = $attribute->export();
		}

		$result = array_merge($result, $this->arrayify_custom()); // TEMP

		$this->flow->step('frozen'); # finish the freezing process

		return $result;
	}


	// TEMP
	protected function arrayify_custom() : array {
		return [];
	}


	### GENERAL METHODS

	final public function is_new() : bool {
		return $this->is_new;
	}


	// TODO explaination
	final public static function has_relationships() : bool {
		foreach(static::get_attribute_definitions() as $definition){
			if($definition->supclass_is(RelationshipList::class)){
				return true;
			}
		}

		return false;
	}


	final public function get_relationships() : ?RelationshipList {
		// TODO check flow state

		foreach(static::get_attribute_definitions() as $attribute => $definition){
			if($definition->supclass_is(RelationshipList::class)){
				return $this->$attribute;
			}
		}

		return null;
	}
}
?>
