<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Properties\Properties;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueExceptionList;
use \Octopus\Core\Model\Cycle\Cycle;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use \Octopus\Core\Model\DataObjectList;
use \Octopus\Core\Model\DataObjectRelation;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use \Octopus\Core\Model\DataObjectCollection;
use Exception;

# What is a DataObject?
# A DataObject is basically a representation of a real thing. Any single object that is handled
# by Octopus (i.e. a blog article, a person profile, an image etc.) is handled as an instance of
# this class.
# DataObjects have properties, in which the actual information is stored. A DataObject that
# represents a person for example has the properties name, date of birth, etc.
# As there are many different kinds of objects in the real world, it would not make much sense
# to wedge all of them into the same class of DataObject. Therefore, for every different kind of
# object, there is a separate class in Octopus defining specific properties for it. This class,
# DataObject, is only the superclass for all of them, containing only properties and methods all
# of these subclasses have in common (like the id and longid properties).
# DataObjects are stored in a database. To make them handleable for Octopus, it is necessary to
# transfer the data from the database into Octopus and, after editing, back from there into the
# database. This class provides all necessary methods to perform such operations. In this sense,
# it serves as a database abstraction layer, similar to an object-relational mapper.


abstract class DataObject {
	// IDEA maybe make id readonly
	protected string $id;		# string of excactly 8 base16 characters; unique identifier of the object; uneditable, randomly generated on create()
	protected ?string $longid;	# string of 9-128 characters (a-z0-9-); another unique identifier; set by the human creating the object, uneditable // IDEA maybe move into the modules

	# this class uses the Properties trait which contains standard methods that handle the properties of this class
	# for documentation on the following definitions, check the Properties trait source file
	use Properties;

	const DB_PREFIX = ''; # prefix of this object's row names in the database (i.e. [prefix]_id, [prefix]_longid)

	const PROPERTIES = [
		# 'id' => 'id',
		# 'longid' => 'longid',
		# ...property definitions for all other properties
	];

	//(abstract) protected static array $properties;

	protected /*readonly*/ null|DataObject|DataObjectList|DataObjectRelation $context;
	protected /*public readonly*/ ?SelectRequest $pull_request;

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.



	### CONSTRUCTION METHODS

	final function __construct(null|DataObject|DataObjectList|DataObjectRelation &$context = null) {
		$this->context = &$context;

		if(!is_null($context)){
			$this->pull_request = null;
		}

		if(!isset(static::$properties)){ # load all property definitions
			static::load_property_definitions();

			if(!static::$properties['id']?->class_is('id')){
				throw new Exception('Invalid propery definitions: valid id definition missing.');
			}
		}

		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'constructed'], 	# cycle entry edge
			['constructed', 'created'], # create()
			['constructed', 'loaded'], 	# pull(), load()
			['created', 'edited'], 		# receive_input(), edit_property()
			['created', 'freezing'], 	# after editing fails, e.g. to inspect the object
			['loaded', 'edited'], 		# receive_input(), edit_property()
			['loaded', 'deleted'], 		# delete()
			['loaded', 'freezing'], 	# freeze(), arrayify()
			['loaded', 'storing'], 		# no impact because object has not yet been altered
			['edited', 'edited'], 		# editing can be done multiple times in sequence
			['edited', 'storing'], 		# push()
			['edited', 'deleted'], 		# nonsense; changes are not being saved
			['edited', 'freezing'], 	# changes are not being saved, but helpful if editing fails
			['storing', 'stored'], 		# two-step storing process
			['storing', 'freezing'], 	# helpful if storing fails
			['stored', 'freezing'], 	# freeze(), arrayify()
			['stored', 'storing'], 		# no impact, but allowed
			['stored', 'edited'], 		# object can be edited again after storing
			['deleted', 'freezing'], 	# object can still be output after deleting
			['deleted', 'deleted'], 	# no impact, but allowed
			['deleted', 'editing'], 	# deleted objects can be edited (and stored again after that)
			['deleted', 'storing'], 	# deleted objects can be re-stored
			['freezing', 'frozen'] 		# two-step freezing process; end
		]);

		$this->cycle->start();
	}


	### INITIALIZATION AND LOADING METHODS

	# Initialize a new, empty object that is not yet stored in the database
	# Generate a random id for the new object and set all properties to null
	final public function create() : void {
		$this->cycle->step('created');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the object. see Properties trait
		$this->initialize_properties();

		$this->create_custom(); # call the custom initialization function
	}


	# ---> see trait Properties
	# final protected static function generate_id() : string;
	# final protected function initialize_properties() : void;


	# the custom initialization function can be used to add class-specific initialization procedures
	protected function create_custom() : void {}


	# Download object data from the database and use load() to load it into this object
	# @param $identifier: the identifier string that specifies which object to download.
	# @param $identify_by: the name of the property $identifier should match to.
	final public function pull(string $identifier, string $identify_by = 'id', array $options = []) : void {
		$this->cycle->check_step('loaded');

		# verify the identify_by value
		$identifying_property = static::$properties[$identify_by] ?? null;
		if(!$identifying_property?->type_is('identifier')){
			throw new Exception("Argument identify_by: property «{$identify_by}» does not exist or is not an identifier.");
		}

		$request = new SelectRequest(static::DB_TABLE);

		foreach(static::$properties as $name => $definition){
			if($definition->supclass_is(DataObject::class)){
				$request->add_join($definition->get_class()::join(on:$definition));
			} else if($definition->supclass_is(DataObjectRelationList::class)){
				$request->add_join($definition->get_class()::join(on:static::$properties['id']));
			} else {
				$request->add_property($definition);
			}
		}

		static::shape_select_request($request, $options);

		$request->set_condition(new IdentifierCondition($identifying_property, $identifier));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			# the database request has failed
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			# the response is empty, meaning that no object with the requested identifier was found
			throw new EmptyResultException($query);
		} else {
			# the database request was successful, use load() to load the data into this object
			$this->load($s->fetchAll());
			$this->pull_request = $request;
		}
	}


	final public static function join(PropertyDefinition $on) : JoinRequest {
		$request = new JoinRequest(static::DB_TABLE, static::get_property_definitions()['id'], $on);

		foreach(static::get_property_definitions() as $name => $definition){
			if($definition->supclass_is(DataObject::class)){
				$request->add_join($definition->get_class()::join(on:$definition));
			} else if(!$definition->supclass_is(DataObjectRelationList::class)){
				$request->add_property($definition);
			}
		}

		static::shape_join_request($request);

		return $request;
	}

	protected static function shape_select_request(SelectRequest &$request, array $options) : void {}
	protected static function shape_join_request(JoinRequest &$request) : void {}


	# the load function loads rows of object data from the database into this object
	# @param $data: single fetched row or multiple rows from the database request's response
	final public function load(array $data) : void {
		$this->cycle->check_step('loaded');

		# $data can have two formats, depending on whether a relationlist was pulled or not:
		# Without Relations: (simple key-value array)
		# 	[
		#		'id' => 'abcdef01',
		#		'longid' => 'example-object',
		#		…
		# 	]
		# With Relations: (nested array)
		# 	[
		#		[
		#			'id' => 'abcdef01',
		#			'relationobject_id' => '12345678',
		#			'relationclass_longid' => 'related-object-1',
		#			…
		#		],
		#		[
		#			'id' => 'abcdef01',
		#			'relationobject_id' => 'abababab',
		#			'relationclass_longid' => 'related-object-2',
		#			…
		#		],
		#		…
		# 	]

		# The first example, a response without relations, has only one row (because only one object was pulled).
		# If relations are pulled using a JOIN statement, the columns of the related object are simply appended to the
		# row containing the base object's columns. If multiple related objects are pulled, for every row, the base
		# object's columns just get repeated (they are basically "filled up" with the same values).
		# To get the columns containing our object data, we must do a distinction:

		if(is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relations
		} else {
			$row = $data; # without relations
		}

		# loop through all property definitions and load the properties
		foreach(static::$properties as $name => $definition){
			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->$name = $row[$definition->get_db_column()]; # for primitive or identifier types, just copy the value

			} else if($definition->type_is('object')){
				if($definition->supclass_is(DataType::class)){
					// TODO


				} else if($definition->supclass_is(DataObject::class)){
					# check whether an object was referenced by checking the column referring to the object
					# that column should contain an id or null, which is from now on stored as $id
					if(empty($row[$definition->get_db_column()])){
						# no object was referenced, set the property to null
						$this->$name = null;
						continue;
					}

					# create a new object of the defined class and load it
					$cls = $definition->get_class();
					$this->$name = new $cls($this); # the argument means context:$this
					$this->$name->load($row);

				} else if($definition->supclass_is(DataObjectRelationList::class)){
					if(isset($this->context)){ # relations are disabled (thus set null) on non-independent objects
						$this->$name = null;
						continue;
					}

					# create and set the relationlist and let it load the relations
					$cls = $definition->get_class();
					$this->$name = new $cls($this); # $this is referenced as context
					$this->$name->load($data);

				} else if($definition->supclass_is(DataObjectCollection::class)){
					// TODO


				}
			}
		}

		$this->load_custom_properties($row); # call the custom loading function to load custom properties

		$this->cycle->step('loaded');
		$this->db->set_synced();
	}


	protected function load_custom_properties(array $row) : void {}


	### EDITING METHODS

	# Edit multiple properties at once, for example to process POST data from an html form
	# @param $data: an array of all new values, with the property name being the key:
	# 	[property_name => new_property_value, ...]
	#	properties that are not contained are ignored
	# @throws: PropertyValueExceptionList
	final public function receive_input(array $data) : void {
		$this->cycle->check_step('edited');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing values)
		$errors = new PropertyValueExceptionList();

		foreach($data as $name => $input){ # loop through all inputs
			if(!isset(static::$properties[$name])){ # check if the property is defined
				continue;
			}

			# try to edit the property
			try {
				$this->edit_property($name, $input);
			} catch(PropertyValueException $e){
				$errors->push($e);
			} catch(PropertyValueExceptionList $e){
				$errors->merge($e, $name);
			}
		}

		# if errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	# ---> see trait Properties
	# final public function edit_property(string $name, mixed $input) : void;
	# protected function edit_custom_property(PropertyDefinition $definition, mixed $input) : void;


	### STORING AND DELETING METHODS

	# Upload this object's data into the database.
	# if this object is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all properties this object contains that are pushable objects are pushed too (recursively).
	# @return: Returns true if a database request was performed for this object, false if not.
	final public function push() : bool {
		# if $this is already at storing right now, do nothing. this prevents endless loops
		if($this->cycle->is_at('storing')){
			return false;
		}

		$this->cycle->step('storing'); # start the storing process

		if($this->db->is_synced()){
			# this object is not newly created and has not been altered, so do not perform any request
			$request = false;
		} else if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest(static::DB_TABLE);
		} else {
			# this object is already stored in the database, but has been altered.
			# perform an update query to update its database values
			$request = new UpdateRequest(static::DB_TABLE);
			$request->set_condition(new IdentifierCondition(static::$properties['id'], $this->id));
		}

		# add the properties to the request and push the dependencies.
		# the object properties this object contains can be pushed before or after this object, depending on whether
		# this object references them (then before) or they reference this object (then after)
		# naturally, a database record can only be referenced if it already exists
		$push_later = [];
		foreach(static::$properties as $name => $definition){
			# if the object is local, all properties are included, otherwise only the alterable ones
			if($request !== false && ($definition->is_alterable() || $this->db->is_local())){
				$request->add_property($definition);
			}

			if($definition->supclass_is(DataObject::class)){
				# single DataObjects are pushed before, so this object can then reference them in the db
				$this->$name?->push();
			} else if($definition->supclass_is(DataObjectRelationList::class)){
				$push_later[] = $name; # RelationLists are pushed after, as they reference this object
			}
		}

		if($request !== false){ # if the request was set to false because this is synced, no db request is performed
			$request->set_values($this->get_push_values());

			$s = $this->db->prepare($request->get_query());
			if(!$s->execute($request->get_values())){
				# the PDOStatement::execute has returned false, so an error occured performing the database request
				throw new DatabaseException($s);
			}
		}

		foreach($push_later as $name){ # push the properties that should be pushed later
			$this->$name?->push();
		}

		$this->cycle->step('stored'); # finish the storing process
		$this->db->set_synced();

		return $request !== false; # return whether a request was performed (for this object only)

		// IDEA: use transactions
	}


	# ---> see trait Properties
	# final protected function get_push_values() : array;
	# protected function get_custom_push_values() : array;


	# Erase this object out of the database.
	# it does not delete objects it contains as properties, but all Relations with this object will be deleted due to
	# the mysql ON DELETE CASCADE constraint.
	# @return: true if a database request was performed, false if not (i.e. because the object still/already is local)
	final public function delete() : bool {
		$this->cycle->check_step('deleted');

		if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so just return false
			$this->cycle->step('deleted');
			return false;
		}

		$request = new DeleteRequest(static::DB_TABLE);
		$request->set_condition(new IdentifierCondition(static::$properties['id'], $this->id));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		}

		$this->cycle->step('deleted');
		$this->db->set_local();

		return true;
	}


	### OUTPUT METHODS

	# ---> see trait Properties
	# final public function freeze() : void;

	# Transforms this object into an array (containing all its properties).
	# properties that are objects themselves are also transformed into arrays using theír own arrayify functions.
	final public function arrayify() : array|null {
		# if this object is already in the freezing process, return null. prevents endless loops
		# this also makes sure that this object only occurs once in the final array
		if($this->cycle->is_at('freezing')){
			return null;
		}

		$this->cycle->step('freezing'); # start the freezing process
		$this->db->disable(); # disable the database access

		$result = [];

		# loop through all properties and copy them to $result. for objects, copy their arrayified version
		foreach(static::$properties as $name => $definition){
			if(!isset($this->$name)){
				$result[$name] = null;
			} else if($definition->type_is('object')){
				$result[$name] = $this->$name?->arrayify();
			} else {
				$result[$name] = $this->$name;
			}
		}

		$this->cycle->step('frozen'); # finish the freezing process

		return $result;
	}
}
?>
