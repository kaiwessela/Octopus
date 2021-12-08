<?php // CODE ??, COMMENTS --, IMPORTS --
namespace Blog\Core\Model;

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

	// IDEA: PropertyDefinitions for custom columns: CustomColumnDefinition?

	protected static array $properties;

	const RELATIONLIST_EXTRACTS = [];

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.



	# ==== STADIUM 1 METHODS (construction) ==== #

	final function __construct() {
		if(!isset($this->properties)){ # load all property definitions
			$this->properties = PropertyDefinition::load($this::PROPERTIES);
		}

		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'constructed'], # cycle entry node
			['constructed', 'created'], # create()
			['constructed', 'loaded'], # pull(), load()
			['created', 'edited'], # receive_input(), edit_property()
			['created', 'freezing'], # after editing fails, e.g. to inspect the object
			['loaded', 'edited'], # receive_input(), edit_property()
			['loaded', 'deleted'], # delete()
			['loaded', 'freezing'], # freeze(), arrayify()
			['loaded', 'storing'], # no impact because object has not yet been altered
			['edited', 'edited'], # editing can be done multiple times in sequence
			['edited', 'storing'], # push()
			['edited', 'deleted'], # nonsense; changes are not being saved
			['edited', 'freezing'], # changes are not being saved, but helpful if editing fails
			['storing', 'stored'], # two-step storing process
			['storing', 'freezing'], # helpful if storing fails
			['stored', 'freezing'], # freeze(), arrayify()
			['stored', 'storing'], # no impact, but allowed
			['stored', 'edited'], # object can be edited another time after storing
			['deleted', 'freezing'], # object can still be output after deleting
			['deleted', 'deleted'], # no impact, but allowed
			['deleted', 'editing'], # deleted objects can be edited (and stored after that)
			['deleted', 'storing'], # deleted objects can be re-stored
			['freezing', 'frozen'] # two-step freezing process; end
		]);

		$this->cycle->start();
	}


	# ==== STADIUM 2 METHODS (initialization/loading) ==== #

	# the create function is used to initialize a new object that is not yet stored in the database
	final public function create() : void {
		$this->cycle->check_step('created');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the object. see Properties trait

		$this->create_custom(); # call the custom initialization function

		$this->cycle->step('created');
	}

x
	# the custom initialization function can be used by concrete DataObject classes
	# to add class-specific initialization procedures
	protected function create_custom() : void {}


	# the pull function downloads object data from the database and uses load() to load it into this object
	# @param $identifier, being the id or longid of an existing object, specifies which object's data to pull
	final public function pull(string $identifier, string $identify_by = 'id') : void {
		$this->cycle->check_step('loaded');

		$identifying_property = $this->properties[$identify_by] ?? null;
		if(is_null($identifying_property) || !$identifying_property->type_is('identifier')){
			throw new InvalidModelCallException("Argument identify_by: property «{$identify_by}» does not exist or is not an identifier.");
		}

		$request = new SelectRequest($this::class);
		$request->set_condition(new IdentifierCondition($identifying_property, $identifier));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			# the database request has failed
			throw new DatabaseException($s);
		} else {
			# the database request was successful
			$r = $s->fetchAll();

			if($s->rowCount() == 0 || empty($r[0][0])){ // TODO this is no more necessary; restructure
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
	final public function load(array $data, bool $relations = true) : void {
		$this->cycle->check_step('loaded');

		# use the load_properties() function from the Properties trait to load the properties
		$this->load_properties($data, relations:$relations);

		$this->cycle->step('loaded');
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
		$this->cycle->check_step('edited');

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

		# if errors occured, throw the buffer exception containing them all
		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	# ==== STADIUM 4 METHODS (storing/deleting) ==== #

	# this function is used to upload this object's data into the database.
	# if this object is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	final public function push() : bool {
		if($this->cycle->is_at('storing')){ // prevents loops
			return false;
		}

		$this->cycle->step('storing');

		if($this->db->is_synced()){
			# this object is not newly created and has not been altered, so just return null
			$this->cycle->step('stored');
			return false;
		} else if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest($this::class);
		} else {
			# this object is already stored in the database, but has been altered.
			# perform an update query to update its database representation
			$request = new UpdateRequest($this::class);
			$request->set_condition(new IdentifierCondition($this->properties['id'], $this->id));
		}

		// TODO set values (former get_push_values())

		$push_before = [];
		$push_after = [];
		foreach($this->properties as $property => $definition){
			if(!$definition->type_is('object')){
				continue;
			}

			if($definition->supclass_is(DataObject::class)){
				$push_before[] = $property;
			} else if($definition->supclass_is(DataObjectRelationList::class)){
				$push_after[] = $property;
			}
		}

		foreach($push_before as $property){
			$this->$property?->push();
		}

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values()){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		}

		foreach($push_after as $property){
			$this->$property?->push();
		}

		$this->cycle->step('stored');
		$this->db->set_synced();

		return true;

		// TODO/IDEA: use transactions
	}


	# this function erases this object out of the database.
	# it does not erase children, but relations might be removed due to the mysql ON DELETE CASCADE constraint.
	# if this object is not yet or anymore stored in the database, this function simply returns false without
	# performing a database request
	final public function delete() : bool {
		$this->cycle->check_step('deleted');

		if($this->db->is_local()){
			# this object is not yet or not anymore stored in the database, so just return null
			$this->cycle->step('deleted');
			return false;
		}

		$request = new DeleteRequest($this::class);
		$request->set_condition(new IdentifierCondition($this->properties['id'], $this->id));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			# the PDOStatement::execute has returned false, so an error occured performing the database request
			throw new DatabaseException($s);
		}

		$this->cycle->step('deleted');
		$this->db->set_local();

		return true;
	}


	# ==== STADIUM 5 METHODS (output) ==== #

	# ---> see trait Properties
	# final public function freeze() : void;
	# final public function arrayify() : array|null;


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
}
?>
