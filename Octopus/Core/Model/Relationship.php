<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Attributes\Attributes;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Requests\InsertRequest;
use \Octopus\Core\Model\Database\Requests\UpdateRequest;
use \Octopus\Core\Model\Database\Requests\DeleteRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\FlowControl\Flow;
use \Octopus\Core\Model\Entity;
use PDOException;
use Exception;

# What is a DataObjectRelation? // TODO
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


abstract class Relationship {
	protected readonly string $id;
	# protected ?[child of Entity] $[name of 1st entity];
	# protected ?[child of Entity] $[name of 2nd entity];
	# ...other attributes

	// TODO collision check
	# this constant defines whether multiple relationships of the same pair of entities should be allowed to exist
	const UNIQUE = false; # true = forbidden; false = allowed

	# this class uses the Attributes trait which contains standard methods that handle the attributes of this class
	# for documentation on the following constants, check the Attributes trait source file
	use Attributes;

	const ATTRIBUTES = [
		# 'id' => 'id',
		# '[name of 1st entity]' => [child of Entity]::class,
		# '[name of 2nd entity]' => [child of Entity]::class,
		# ...attribute definitions for all other attributes
	];

	# all child classes must set the following property:
	# protected static array $attributes;

	public readonly Entity $context;
	protected readonly string $joined_entity_attribute;

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Flow $flow; # this class uses the Flow class to control the order of its method calls. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(Entity &$context) {
		$this->context = $context;

		$this->db = new DatabaseAccess();

		$this->flow = new Flow([ // TODO
			['root', 'construct'],
			['construct', 'init/load'],
			['init/load', 'edit'], ['init/load', 'store/delete'], ['init/load', 'output'],
			['edit', 'edit'], ['edit', 'store/delete'], ['edit', 'output'],
			['store/delete', 'store/delete'], ['store/delete', 'output'], ['store/delete', 'edit'],
			['output', 'output']
		]);

		$this->flow->start();

		if(!isset(static::$attributes)){ # load all attribute definitions
			static::load_attribute_definitions();
		}

		# note: the possibility that someone defines a relation with both entities being of the same class is not
		# prevented here. however, there is no use case for that constellation, and at the latest on a pull(), a
		# DatabaseException will be thrown due to column names not being distinct. just hope nobody is that stupid.

		$id_found = false;
		foreach(static::$attributes as $name => &$definition){ # validate the attribute definitions
			if($definition->class_is('id')){
				$id_found = true;
			} else if($definition->supclass_is(Entity::class)){
				if($definition->class_is($this->context::class)){
					$this->$name = &$this->context;
				} else {
					$this->joined_entity_attribute = $name;
				}

				$definition->set_alterable(false);
				$definition->set_required(true);
			} else if(!$definition->type_is('primitive')){
				throw new Exception('Invalid attribute definition: only entities, primitives and id are allowed.');
			}
		}

		if(!$id_found){
			throw new Exception('Invalid attribute definitions: valid id definition missing.');
		}
	}


	### INITIALIZATION AND LOADING METHODS

	# Initialize a new relationship that is not yet stored in the database
	# Generate a random id for the new relationship and set all attributes to null
	final public function create() : void {
		$this->flow->step('init/load');
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the relationship. (--> Properties)
		$this->initialize_attributes();
	}


	# ---> see trait Attributes
	# final protected static function generate_id() : string;
	# final protected function initialize_attributes() : void;


	# Load rows of relationship and entity data from the database into this Relationship object
	# @param $data: single fetched row or multiple rows from the database request's response
	final public function load(array $data) : void {
		$this->flow->check_step('init/load');

		# basically the same procedure as in Entity::load(), but shorter
		foreach(static::$attributes as $name => $definition){
			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->$name = $data[$definition->get_db_column()]; # for primitives and identifiers just copy the value

			} else if($definition->supclass_is(Entity::class)){
				if(isset($this->$name)){ # if the entity is alredy set, it is the context entity, so skip loading it
					continue;
				}

				# entity attributes in relations cannot be null/unset, so simply create the entity and load it
				$cls = $definition->get_class();
				$this->$name = new $cls($this); # reference this relationship as context
				$this->$name->load($data);
			}
		}

		$this->flow->step('init/load');
		$this->db->set_synced();
	}


	### EDITING METHODS

	# Edit multiple attributes at once, for example to process POST data from an html form
	# @param $data: an array of all new values, with the attribute name being the key:
	# 	[attribute_name => new_attribute_value, ...]
	#	attributes that are not contained are ignored
	# @throws: AttributeValueExceptionList
	final public function receive_input(array $input) : void {
		$this->flow->check_step('edit');

		# create a new container exception that buffers and stores all AttributeValueExceptions
		# that occur during the editing of the attribute (i.e. invalid or missing inputs)
		$errors = new AttributeValueExceptionList();

		foreach($data as $name => $input){ # loop through all input fields
			if(!isset(static::$attributes[$name])){
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

		$this->flow->step('edit');
	}


	# ---> see trait Attributes
	# final public function edit_attribute(string $name, mixed $input) : void;
	# protected function edit_custom_attribute(AttributeDefinition $definition, mixed $input) : void;


	# Set the joined entity; shortcut for edit_attribute([joined entity name], [value])
	final public function set_joined_entity(Entity &$entity) : void {
		$this->edit_attribute(static::$attributes[$this->joined_entity_attribute], $entity);
	}


	### STORING AND DELETING METHODS

	# Upload this relationship's data into the database.
	# if this relationship is not newly created and has not been altered, no database request is executed
	# and this function returns false. otherwise, if a database request was executed successfully, it returns true.
	# all attributes this relationship contains that are Entities themselves are pushed too (recursively).
	# @return: true if a database request was performed for this entity, false if not.
	final public function push() : bool {
		# if $this is already at storing right now, do nothing. this prevents endless loops
		if($this->flow->is_at('storing')){
			return false;
		}

		$this->flow->step('storing'); # start the storing process

		if($this->db->is_synced()){
			# this relationship is not newly created and has not been altered, so do not perform a request
			$request = false;
		} else if($this->db->is_local()){
			# this relationship is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest(static::DB_TABLE);
		} else {
			# this relationship is already stored in the database, but has been altered.
			# perform an update query to update its database values

			# if the relationship is not updatable because it has no attributes to be updated, this method
			# automatically returns false because it then also cannot be altered and always remains synced
			$request = new UpdateRequest(static::DB_TABLE);
			$request->set_condition(new IdentifierEquals($this->properties['id'], $this->id));
		}

		# add the attributes to the request and push the entities this relationship contains
		foreach(static::$attributes as $name => $definition){
			# if this is local, all attributes are included, otherwise only the alterable ones
			if($request !== false && ($definition->is_alterable() || $this->db->is_local())){
				$request->add_attribute($definition);
			}

			if($definition->supclass_is(Entity::class)){
				$this->$name?->push();
			}
		}

		if($request !== false){ # if the request was set to false because this is synced, no db request is performed
			$request->set_values($this->get_push_values());

			try {
				$s = $this->db->prepare($request->get_query());
				$s->execute($request->get_values());
			} catch(PDOException $e){
				throw new DatabaseException($e, $s);
			}
		}

		$this->flow->step('stored'); # finish the storing process
		$this->db->set_synced();

		return $request !== false; # return whether a request was performed (for this relationship only)
	}


	# ---> see trait Attributes
	# final protected function get_push_values() : array;


	# Erase this relationship out of the database.
	# this does not delete entities it contains as attributes.
	# @return: true if a database request was performed, false if not (i.e. because $this is still/already local)
	final public function delete() : bool {
		$this->flow->check_step('store/delete');

		if($this->db->is_local()){
			# this relationship is not yet or not anymore stored in the database, so just return false
			$this->flow->step('store/delete');
			return false;
		}

		# create a DeleteRequest and set the WHERE condition to id = $this->id
		$request = new DeleteRequest(static::DB_TABLE);
		$request->set_condition(new IdentifierEquals(static::$attributes['id'], $this->id));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		$this->flow->step('store/delete');
		$this->db->set_local();

		return true;
	}


	### OUTPUT METHODS

	# ---> see trait Attributes
	# final public function freeze() : void;


	# Return the arrayified joined entity
	final public function arrayify() : array {
		$this->flow->step('freezing');
		$this->db->disable(); # disable the database access
		$this->flow->step('frozen');

		return $this->get_joined_entity()->arrayify();
	}


	# Return the joined entity; if it is not set yet, return null
	final public function &get_joined_entity() : ?Entity {
		if(!isset($this->joined_entity_attribute)){
			return null;
		}

		return $this->{$this->joined_entity_attribute};
	}
}
?>
