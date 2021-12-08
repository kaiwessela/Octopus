<?php // CODE ??, COMMENTS --, IMPORTS --
namespace Blog\Core\Model;

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
	# public [child of DataObject] $[name of 1st object];
	# public [child of DataObject] $[name of 2nd object];
	# ...other properties

	// TODO check this
	# this constant defines whether multiple relations of the same pair of DataObjects should be allowed to exist
	const UNIQUE; # true = forbidden; false = allowed

	// XXX DEPRECATED
	# this defines which two DataObject classes the relation consists of
	const OBJECTS = [
		# '[name of 1st object]' => [child of DataObject]::class,
		# '[name of 2nd object]' => [child of DataObject]::class
	];
	// xxx end

	protected static array $objects;

	# this class uses the Properties trait which contains standard methods that handle the properties of this class
	# for documentation on the following constants, check the Properties trait source file
	use Properties;

	const PROPERTIES = [
		# 'id' => 'id',
		# ...property definitions for all other properties (except the two DataObjects)
	];

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

		$this->load_property_definitions();

		// NOTE: the possibility that someone defines a relation with both objects being of the same class is not
		// prevented here. however, there is no use case for that constellation, and at the latest on a pull(), a
		// DatabaseException will be thrown due to column names not being distinct. just hope nobody is that stupid.

		foreach($this->properties as $name => &$definition){
			if($definition->type_is('object')){
				if($definition->supclass_is(DataObject::class)){
					$definition->set_alterable(false);
					$this->objects[$name] = $definition;
				} else {
					throw new Exception();
				}
			} else if($definition->type_is('custom')){
				throw new Exception();
			}
		}

		if($object_count !== 2){
			throw new Exception();
		}
	}


	# ==== INITIALIZATION AND LOADING METHODS ==== #

	# this function is used to initialize a new relation that is not yet stored in the database (newly created)
	final public function create() : void {
		$this->cycle->step('init/load');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the object. see Properties trait
	}


	# this function loads rows of relation and object data from the database into this relation
	# it receives the base object and initializes and loads the second object based on the database data,
 	# so in order for this to work, the second object's data must be selected using a join.
	# @param $data: single fetched row or multiple rows from the database request's response
	# @param $object: the base object of this relation, one of the two specified in $this::OBJECTS
	final public function load(array $data, ?DataObject $base_object = null) : void {
		$this->cycle->check_step('init/load');

		$this->load_properties($data, relation_base_object:$base_object);

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

		# loop through all property definitions and edit the respective properties
		foreach($this->properties as $name => $_){
			try {
				$this->edit_property($name, $input[$name] ?? null);
			} catch(PropertyValueException $e){
				$errors->push($e);
			} catch(InputFailedException $e){
				$errors->merge($e, $name);
			}
		}

		// TODO check if both objects have been set

		# if errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->cycle->step('edit');
	}


	final public function set_object(DataObject $object) : void {
		foreach($this->objects as $name => $definition){
			if($definition->class_is($object::class)){
				$this->edit_property($name, $object);
				return;
			}
		}

		throw new InvalidModelCallException(); // TODO
	}


	# ==== STORING AND DELETING METHODS ==== #

	# this function is used to upload this relation's data into the database.
	# if this relation is not newly created and has not been altered, no database request is executed
	# and this function simply returns null.
	final public function push() : null|void {
		if($this->cycle->is_at('storing')){ // prevents loops
			return null;
		}

		$this->cycle->step('storing');

		if($this->db->is_synced()){
			# this object is not newly created and has not been altered, so just return null
			$this->cycle->step('stored');
			return null;
		}

		if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest($this::class);
		} else { # $this->db->is_altered()
			# this object is already stored in the database, but has been altered.
			# perform an update query to update its database representation

			# if the relation is not updatable because it has no properties to be updated, this function automatically
			# returns null because it then also cannot be altered and always remains synced
			$request = new UpdateRequest($this::class);
			$request->set_condition(new IdentifierCondition($this->properties['id'], $this->id));
		}

		foreach($this->objects as $property => $_){
			$this->$property->push();
		}

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($this->get_push_values())){ // TODO
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		} else {
			$this->cycle->step('stored');
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
	// XXX DEPRECATED
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
	// xxx end


	# ==== GENERAL METHODS ==== #

	final public function get_object(string $class) : ?DataObject { // TODO unfinished, comments
		foreach($this::OBJECTS as $property => $cls){
			if($cls === $class){
				return $this->$property;
			}
		}

		return null;
	}
}
?>
