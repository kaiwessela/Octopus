<?php // CODE ok, COMMENTS ok, IMPORTS --
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\Traits\DBTrait;
use \Blog\Model\Abstracts\Traits\StateTrait;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\IdentifierMismatchException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;

# What is a DataObjectRelation?
# A DataObjectRelation is a construct used to create and store many-to-many relations between two classes of DataObjects.
# To illustrate: Imagine you have a blog with some articles about various topics. What you might want to do is to
# categorize them by their topic, so that your readers can search by category and find your articles more easily.
# To each category, you might also want to write a little introductory text.
# So now you need to have two tables in your Database (and two respective subclasses of DataObject):
# Articles (with i.e. a title, the author's name and content) and Categories (with a name and an introduction).
# The difficulty is that each article should be able to have multiple categories and (obviously) each category can
# include multiple articles. This construct is called a many-to-many relationship.
# To store this in the database, you not only need the two object tables, but also a junction table containing a
# list of all existing relations between these objects (search for "many-to-many relation" for detailed explainations).
# So each instance of this class constitutes a relation between two objects. Its data is stored as a row in the
# junction table.
# This class is an abstract class, so for every pair of dataobject classes related to each other, there has to be
# a separate 'concrete' child class of this containing details (for the two classes Category and Article, there might
# be a class ArticleCategoryRelation).
# Every concrete subclass of this has to define two private properties for the DataObjects related to each other, so
# i.e. the class ArticleCategoryRelation would have the following properties:
#	protected ?Article $article;
#	protected ?Category $category;
# Each instance of DataObjectRelation also gets an unique, randomly-generated id (works and looks the same as
# the id of a DataObject and has the same function).
# It is possible to define other properties, just as for concrete DataObjects. These properties, however, can only be
# of the type 'primitive'.


abstract class DataObjectRelation {
	public string $id;
	# public ?[child of DataObject] $[name of 1st object];
	# public ?[child of DataObject] $[name of 2nd object];
	# ...other properties

	# this constant defines whether multiple relations of the same pair of DataObjects should be allowed to exist
	const UNIQUE; # true = forbidden; false = allowed

	# this defines which two DataObject classes the relation consists of
	const OBJECTS = [
		# '[name of 1st object]' => [child of DataObject]::class,
		# '[name of 2nd object]' => [child of DataObject]::class
	];

	# this class uses the Properties trait which contains standard methods that handle the properties of this class
	# for documentation on the following constants, check the Properties trait source file
	use Properties;

	const PROPERTIES = [
		# 'id' => 'id',
		# ...property definitions for all other properties (except the two DataObjects)
	];

	final const ALLOWED_PROPERTY_TYPES = ['special', 'primitive'];

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	# ==== CONSTRUCTION METHODS ==== #

	final function __construct() {
		$this->db = new DatabaseAccess();

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


	# ==== INITIALIZATION AND LOADING METHODS ==== #
	# a relation is always loaded or created based on an existing object which is forwarded as the $object argument.
	# the second object is then either initialized and loaded by this relation's load function based on database
	# rows or it must be installed via edit_property() for freshly created relations.

	# this function is used to initialize a new relation that is not yet stored in the database (newly created)
	# @param $object: the base object of this relation (see comments above), one of the two specified in $this::OBJECTS
	final public function create(DataObject &$object) : void { // BUG this might cause problems, maybe comment out DataObject
		$this->cycle->step('init/load');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the object. see Properties trait

		# check whether the base object is of the correct class
		if(!in_array($object::class, $this::OBJECTS)){
			throw new TypeError('Invalid Type of $object: '.$object::class);
		}

		# set the base object as one of the two object properties.
		# the other object property (that is not yet set and known) is set to null
		foreach($this::OBJECTS as $property => $class){
			if($object::class === $class){
				$this->edit_property($property, $object);
			} else {
				$this->$property = null;
			}
		}
	}


	# this function loads rows of relation and object data from the database into this relation
	# it receives the base object and initializes and loads the second object based on the database data,
 	# so in order for this to work, the second object's data must be selected using a join.
	# @param $data: single fetched row or multiple rows from the database request's response
	# @param $object: the base object of this relation, one of the two specified in $this::OBJECTS
	final public function load(array $data, DataObject &$object) : void {
		$this->cycle->check_step('init/load');

		# check whether the base object is of the correct class
		if(!in_array($object::class, $this::OBJECTS)){
			throw new TypeError('Invalid Type of $object: '.$object::class);
		}

		# set the base object as one of the two object properties.
		# initialize and load the other object using the db request's response data
		foreach($this::OBJECTS as $property => $class){
			if($object::class === $class){
				$this->$property = $object;
			} else {
				$this->$property = new $class();
				$this->$property->load($data, norelations:true, nocollections:true);
			}
		}

		$this->load_properties($data, norelations:true, nocollections:true);

		$this->cycle->step('init/load');
		$this->db->set_synced();
	}


	# ==== EDITING METHODS ==== #

	# this function is used to edit multiple properties at once, for example to process POST data from an html
	# form that contains data for multiple properties.
	# Important: this function edits the entire object, not only properties that are contained in $data.
	# properties that don't have a value in $data are set to null, throwing an error if that is not possible.
	# @param $input: [property_name => new_property_value, ...]
	final public function receive_input(array $input) : void {
		$this->cycle->check_step('edit');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing inputs)
		$errors = new InputFailedException();

		# loop through the defined object definitions and check and edit the objects
		foreach($this::OBJECTS as $name => $class){
			if(!is_subclass_of($class, DataObject::class)){
				throw new Exception("DataObjectRelation » Invalid Object Definition: $class is not a DataObject.");
			}

			# if the property is already set with an object, continue
			# that also prevents from changing an object to another, so effectively an object can only be set once
			if(!empty($this->$name)){
				continue;
			}

			# from here, this processes a request to set an object for the first time
			$value = $input[$name];

			# the object can be received in various ways: as a loaded DataObject, as an id, as object data etc.
			if(empty($value)){
				# no object, object data or id given
				# MissingValueException requires a PropertyDefinition for the first argument, but the object definition
				# of a relation does not use PropertyDefinitions regularly, so one needs to be created for the exception
				$errors->push(new MissingValueException(new PropertyDefinition($name, $class)));

			} else if($value instanceof DataObject){
				# the input object is already an initialized DataObject
				try {
					$value->push(); # try to push the object to ensure it is stored on the database
				} catch(PropertyValueException $e){ # NOTE: OutOfCycleException can occur but is not caught by this
					# the object might be empty or incomplete, so it cannot be set
					$errors->push($e, $name);
					continue;
				}

				$this->$name = $value;

			} else if(is_string($value) || (is_array($value) && !empty($value['id']))){
				# the input is assumed to be the id of an existing object
				# the input can either be an id string or an array containing a value 'id' that is an id string

				$id = $value['id'] ?? $value;
				$object = new $class(); # initialize a new instance of the object's class

				try {
					$object->pull($id); # try to pull the object with the given id
					$this->$name = $object;
				} catch(EmptyResultException $e){
					# there was no object found with this id
					# see try/catch above on why a PropertyDefinition is created here
					$errors->push(new RelationObjectNotFoundException(new PropertyDefinition($name, $class), $id));
				}

			} else if(is_array($value)){
				# the input is assumed to be an array with data to create a new DataObject from

				$object = new $class(); # initialize a new instance of the object's class

				try {
					# try to create a new DataObject and fill it with the input
					$object->create();
					$object->receive_input($value);
					$object->push();
					$this->$name = $object;
				} catch(InputFailedException $e){
					$errors->merge($e, $name);
				}

			} else {
				# the input data received for the object are invalid
				$errors->push(new IllegalValueException(new PropertyDefinition($name, $class), $value));
			}
		}

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


	# ==== STORING AND DELETING METHODS ==== #

	# this function is used to upload this relation's data into the database.
	# if this relation is not newly created and has not been altered, no database request is executed
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
		} else { # $this->db->is_altered()
			# this object is already stored in the database, but has been altered.
			# perform an update query to update its database representation

			# if UPDATE_QUERY is null, thus the relation is not updatable because it has no properties to be updated,
			# this function automatically returns null because it then also cannot be altered and always remains synced
			$s = $this->db->prepare($this::UPDATE_QUERY);
		}

		if(!$s->execute($this->get_push_values())){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		} else {
			$this->cycle->step('store/delete');
			$this->db->set_synced();
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


	# ==== OUTPUT METHODS ==== #

	# this function disables the database access for this object and both of its object properties.
	# it should be called by all controllers handing over this object to templates etc. in order to output it.
	# this is a safety feature that prevents templates from altering or deleting object data
	final public function freeze() : void {
		$this->cycle->step('output');
		$this->db->disable();

		# freeze objects
		foreach($this::OBJECTS as $property => $_){
			$this->$property->freeze();
		}
	}


	# this function transforms this object into an array containing all of its properties.
	# properties that are objects themselves get also transformed into arrays using theír own arrayify functions.
	# If this function is called recursively from a DataObject that itself is included in this relation, $perspective
	# should be filled with that object's class name in order to prevent an unnecessary duplicate output of the object.
	# @param $perspective: the fully qualified class name of the object that should not be included in the array
	final public function arrayify(?string $perspective = null) : ?array {
		$this->cycle->step('output');
		$this->db->disable();

		$result = [];

		foreach($this::OBJECTS as $property => $class){
			if($perspective !== $class){
				# if $perspective equals the class name, simply do not include the object in the array
				# there is no problem with an endless loop because load(norelations:true) already prevents that
				$result[$property] = $this->$property->arrayify();
			}
		}

		foreach($this::PROPERTIES as $property => $_){
			$result[$property] = $this->$property;
		}
	}


	# ==== GENERAL METHODS ==== #

	final public function get_object(string $class) : ?DataObject { // TODO unfinished, comments
		foreach($this::OBJECTS as $property => $cls){
			if($cls === $class){
				return $this->$property;
			}
		}

		return null;
	}


	# ==== DATABASE QUERIES ==== #
	const INSERT_QUERY; # 'INSERT INTO [table_name] ([...column_names]) VALUES ([...placeholders])'
	const UPDATE_QUERY; # null | 'UPDATE [table_name] SET [column_name] = [placeholder][, ...] WHERE [id_column] = :id'
	const DELETE_QUERY; # 'DELETE FROM [table_name] WHERE [id_column] = :id'
}
?>
