<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\Traits\DatabaseAccess;
use \Blog\Model\Abstracts\Traits\Cycle;
use \Blog\Model\Abstracts\DataType;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\Abstracts\DataObjectCollection;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;
use \Blog\Model\Exceptions\IdentifierCollisionException;
use \Blog\Model\Exceptions\IdentifierMismatchException;
use InvalidArgumentException;
use Exception;
use TypeError;

# What is a DataObject?
# A DataObject is a collection of properties of a real thing. Any single object that is handled
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
	protected string $id;		# string of excactly 8 base16 characters; unique identifier of the object; uneditable, randomly generated on create()
	protected string $longid;	# string of 9-128 characters (a-z0-9-); another unique identifier; set by the human creating the object, uneditable

	# this class uses the Properties trait which contains standard methods that handle the properties of this class
	# for documentation on the following constants, check the Properties trait source file
	use Properties;

	const DB_PREFIX; # prefix of this object's row names in the database (i.e. [prefix]_id, [prefix]_longid)

	const PROPERTIES = [
		# 'id' => 'id',
		# 'longid' => 'longid',
		# ...property definitions for all other properties
	];

	const RELATIONLIST_EXTRACTS = [];

	final const ALLOWED_PROPERTY_TYPES = ['special', 'primitive', 'object', 'custom'];

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.



	# ==== STADIUM 1 METHODS (construction) ==== #

	final function __construct() {
		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'construct'],
			['construct', 'init/load'],
			['init/load', 'edit'], ['init/load', 'store/delete'], ['init/load', 'output'],
			['edit', 'edit'], ['edit', 'store/delete'], ['edit', 'output'],
			['store/delete', 'store/delete'], ['store/delete', 'output'], ['store/delete', 'edit'],
			['output', 'output']
		]);

		$this->cycle->start();
	}


	# ==== STADIUM 2 METHODS (initialization/loading) ==== #

	# the create function is used to initialize a new object that is not yet stored in the database
	final public function create() : void {
		$this->cycle->step('init/load');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the object. see Properties trait

		$this->create_custom(); # call the custom initialization function
	}

	# the custom initialization function can be used by concrete DataObject classes
	# to add class-specific initialization procedures
	protected function create_custom() : void {}


	# the pull function downloads object data from the database and uses load() to load it into this object
	# @param $identifier, being the id or longid of an existing object, specifies which object's data to pull
	final public function pull(string $identifier) : void {
		$this->cycle->check_step('init/load'); # check is used here because pull failures are expected
		$this->db->enable(); # connect to the database

		$query = $this::PULL_QUERY;
		$s = $this->db->prepare($query);
		if(!$s->execute(['id' => $identifier])){
			# the database request has failed
			throw new DatabaseException($s);
		} else {
			# the database request was successful
			$r = $s->fetchAll();

			if($s->rowCount() == 0 || empty($r[0][0])){
				# the response is empty, meaning that no object with the requested identifier was found
				throw new EmptyResultException($query);
			} else {
				# an object was found, use load() to load its data into this object
				$this->load($r);
			}
		}
	}


	# the load function loads rows of object data from the database into this object
	# @param $data: single fetched row or multiple rows from the database request's response
	# @param $norelations: prevents loading of DataObjectRelation(List) properties. use to prevent an endless loop
	# @param $nocollections: prevents loading of DataObjectCollection properties. mostly performance reasons
	final public function load(array $data, bool $norelations = false, bool $nocollections = false) : void {
		$this->cycle->check_step('init/load');

		# use the load_properties() function from the Properties trait to load the properties
		$this->load_properties($data, $norelations, $nocollections);

		$this->cycle->step('init/load');
		$this->db->set_synced();
	}


	# ==== STADIUM 3 METHODS (editing) ==== #

	# this function is used to edit multiple properties at once, for example to process POST data from an html
	# form that contains data for multiple properties
	# Important: this function edits the entire object, not only properties that are contained in $data.
	# properties that don't have a value in $data are set to null, throwing an error if that is not possible.
	# @param $input: [property_name => new_property_value, ...]
	# @exceptions: InputFailedException
	final public function receive_input(array $input) : void {
		$this->cycle->check_step('edit');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing inputs)
		$errors = new InputFailedException();

		# loop through all property definitions and edit the respective properties
		foreach($this::PROPERTIES as $name => $_){
			try {
				$this->edit_property($name, $input[$name] ?? null);
			} catch(PropertyValueException $e){
				$errors->push($e);
			} catch(InputFailedException $e){
				$errors->merge($e, $name);
			}
		}

		# if errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->cycle->step('edit');
	}


	# ==== STADIUM 4 METHODS (storing/deleting) ==== #

	# this function is used to upload this object's data into the database.
	# if this object is not newly created and has not been altered, no database request is executed
	# and this function simply returns null.
	final public function push() : null|void {
		$this->cycle->check_step('store/delete');

		if($this->db->is_synced()){
			# this object is not newly created and has not been altered, so just return null
			$this->cycle->step('store/delete');
			return null;
		}

		$this->db->enable();

		if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so perform an insert query
			$s = $this->db->prepare($this::INSERT_QUERY);
		} else {
			# this object is already stored in the database, but has been altered.
			# perform an update query to update its database representation
			$s = $this->db->prepare($this::UPDATE_QUERY);
		}

		if(!$s->execute($this->get_push_values())){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		} else {
			$this->cycle->step('store/delete');
			$this->db->set_synced();
		}

		foreach($this::PROPERTIES as $property => $definition){ // TODO maybe move this up and use transactions
			if(is_subclass_of($definition, DataObject::class)){
				$this->$property?->push();
			} else if(is_subclass_of($definition, DataObjectRelationList::class)){
				$this->$property?->push();
			}
		}
	}


	# this function erases this object out of the database.
	# it does not erase children, but relations might be removed due to the mysql ON DELETE CASCADE constraint.
	# if this object is not yet or anymore stored in the database, this function simply returns null without
	# performing a database request
	final public function delete() : null|void {
		$this->cycle->check_step('store/delete');

		if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so just return null
			$this->cycle->step('store/delete');
			return null;
		}

		$this->db->enable();

		$s = $this->db->prepare($this::DELETE_QUERY);
		if(!$s->execute(['id' => $this->id])){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		} else {
			$this->cycle->step('store/delete');
			$this->db->set_local();
		}
	}


	# ==== STADIUM 5 METHODS (output) ==== #

	# this function disables the database access for this object and all other objects it contains.
	# it should be called by all controllers handing over this object to templates etc. in order to output it.
	# this is a safety feature that prevents templates from altering or deleting object data
	final public function freeze() : void {
		$this->cycle->step('output');
		$this->db->disable();

		# loop through all properties and freeze them recursively if they are freezable
		foreach($this::PROPERTIES as $property => $_){
			if($this->$property instanceof DataObject){
				$this->$property->freeze();
			} else if($this->$property instanceof DataObjectList){
				$this->$property->freeze();
			} else if($this->$property instanceof DataObjectRelationList){
				$this->$property->freeze($this::class);
			}
		}
	}


	# this function transforms this object into an array containing all of its properties.
	# properties that are objects themselves get also transformed into arrays using theír own arrayify functions.
	final public function arrayify() : ?array {
		$this->cycle->step('output');
		$this->db->disable();

		$result = [];

		foreach($this::PROPERTIES as $property => $_){
			# use arrayify functions on object properties to turn them into an array
			# copy all property values into $result
			$result[$property] = match(true){
				$this->$property instanceof DataObject ||
				$this->$property instanceof DataObjectList ||
				$this->$property instanceof DataObjectCollection
					=> $this->$property->arrayify(),
				$this->$property instanceof DataObjectRelationList
					=> $this->$property->arrayify($this::class), # see DORelationList on why $this::class arg. is needed
				default => $this->$property;
			}
		}

		return $result;
	}



	### HELPER METHODS

	function __get($property) {
		if(!empty($this::RELATIONLIST_EXTRACTS[$property])){
			$object_list_class = $this::RELATIONLIST_EXTRACTS[$property][0];
			$relationlist_name = $this::RELATIONLIST_EXTRACTS[$property][1];

			if(!is_subclass_of($object_list_class, DataObjectList::class)){
				// Exception
			}

			if(!$this->$relationlist_name instanceof DataObjectRelationList){
				// Exception
			}

			if(empty($this->$relationlist_name?->relations)){
				$object_list = null;
			} else {
				$object_list = new $object_list_class();
				$object_list->extract_from_relationlist($this->$relationlist_name);
			}

			if($this->is_frozen()){
				$this->$property = $object_list;
			}

			return $object_list;
		}
	}


	# ==== DATABASE QUERIES ==== #
	const PULL_QUERY; # 'SELECT * FROM [table_name] [joins] WHERE [id_column] = :id OR [longid_column] = :id'
	# join for DataObjects = 'LEFT JOIN [table_name] ON [id_column] = [local_id_column]'
	# join for relations = 'LEFT JOIN [join_table_name] ON [join_obj_id_column] = [id_column]
	#						LEFT JOIN [joined_table_name] ON [joined_id_column] = [join_obj_id_column]'

	const INSERT_QUERY; # 'INSERT INTO [table_name] ([...column_names]) VALUES ([...placeholders])'
	const UPDATE_QUERY; # 'UPDATE [table_name] SET [column_name] = [placeholder][, ...] WHERE [id_column] = :id'
	const DELETE_QUERY; # 'DELETE FROM [table_name] WHERE [id_column] = :id'
}
?>
