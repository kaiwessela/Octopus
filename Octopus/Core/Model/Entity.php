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
	protected readonly string $id;	# main unique identifier of the object; uneditable; randomly generated on create()
	protected ?string $longid;		# another unique identifier; editable; set by the user

	# this class uses the Attributes trait which contains standard methods that handle the attributes of this class
	# for documentation on the following definitions, check the Attributes trait source file
	use Attributes;

	const DB_PREFIX = ''; # prefix of this object's row names in the database (i.e. [prefix]_id, [prefix]_longid)

	const ATTRIBUTES = [
		# 'id' => 'id',
		# 'longid' => 'longid',
		# ...raw attribute definitions for all other attributes
	];

	# all child classes must set the following property:
	# protected static array $attributes;

	public readonly null|Entity|EntityList|Relationship $context;
	public readonly ?SelectRequest $pull_request;

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Flow $flow; # this class uses the Flow class to control the order of its method calls. see there for more.



	### CONSTRUCTION METHODS

	final function __construct(null|Entity|EntityList|Relationship &$context = null) {
		$this->context = &$context;

		if(!is_null($context)){
			$this->pull_request = null;
		}

		if(!isset(static::$attributes)){ # load all attribute definitions
			static::load_attribute_definitions();

			if(!static::$attributes['id']?->class_is('id')){
				throw new Exception('Invalid attribute definitions: valid id definition missing.');
			}
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
		$this->db->set_local();

		$this->id = self::generate_id(); # generate and set a random, unique id for the entity. (--> trait Attributes)
		$this->initialize_attributes();

		$this->create_custom(); # call the custom initialization method
	}


	# ---> see trait Attributes
	# final protected static function generate_id() : string;
	# final protected function initialize_attributes() : void;


	# the custom initialization method can be used to add class-specific initialization procedures
	protected function create_custom() : void {}


	# Download entity data from the database and use load() to load it into this Entity object
	# @param $identifier: the identifier string that specifies which entity to download.
	# @param $identify_by: the name of the attribute $identifier is matched with.
	# @param $options: additional, custom pull options
	final public function pull(string $identifier, string $identify_by = 'id', array $options = []) : void {
		$this->flow->check_step('loaded');

		# verify the identify_by value
		$identifying_attribute = static::$attributes[$identify_by] ?? null;
		if(!$identifying_attribute?->type_is('identifier')){
			throw new Exception("Argument identify_by: attribute «{$identify_by}» not found or is not an identifier.");
		}

		$request = new SelectRequest(static::DB_TABLE);

		foreach(static::$attributes as $name => $attribute){ # $attribute is an AttributeDefinition
			if($attribute->supclass_is(Entity::class)){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # join Entity attributes recursively
				$request->add_attribute($attribute); // FIXME this is a hotfix. entities need not only be joined, but also pulled. i.e. the key post_image_id must be in the values, not only image_id. that is because load needs this value to check if it is set.
			} else if($attribute->supclass_is(RelationshipList::class)){
				$request->add_join($attribute->get_class()::join(on:static::$attributes['id'])); # join RelationshipList
			} else if($attribute->is_pullable()){
				$request->add_attribute($attribute);
			}
		}

		static::shape_select_request($request, $options);

		$request->set_condition(new IdentifierEquals($identifying_attribute, $identifier));

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
	final public static function join(AttributeDefinition $on) : JoinRequest {
		$request = new JoinRequest(static::DB_TABLE, static::get_attribute_definitions()['id'], $on);

		foreach(static::get_attribute_definitions() as $name => $attribute){ # $attribute is an AttributeDefinition
			if($attribute->supclass_is(Entity::class)){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # recursively join entities this entity contains
			} else if($attribute->is_pullable()){
				$request->add_attribute($attribute);
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

		# $data can have two formats, depending on whether relationships were pulled or not:
		# Without Relationships: (simple key-value array)
		# 	[
		#		'id' => 'abcdef01',
		#		'longid' => 'example-object',
		#		…
		# 	], …
		# With Relationships: (nested array)
		# 	[
		#		[
		#			'id' => 'abcdef01',
		#			'relationship_id' => '12345678',
		#			'relationship_entity_longid' => 'joined-object-1',
		#			…
		#		],
		#		[
		#			'id' => 'abcdef01',
		#			'relationship_id' => 'abababab',
		#			'relationship_entity_longid' => 'joined-object-2',
		#			…
		#		], …
		# 	]

		# The first example, a response without relationships, has only one row (because only one entity was pulled).
		# If relationships are pulled using a JOIN statement, the columns of the joined entity are simply appended to
		# the row containing the base entity’s columns. If multiple related entities are pulled, for every row, the base
		# entity’s columns just get repeated (they are basically "filled up" with the same values).

		# To parse the columns containing our entity data, we must do a distinction:
		if(is_array($data[0])){ # check whether the data array is nested
			$row = $data[0]; # with relationships
		} else {
			$row = $data; # without relationships
		}

		# loop through all attribute definitions and load the attributes
		foreach(static::$attributes as $name => $definition){
			if($definition->type_is('contextual')){
				continue;
			} else if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->$name = $row[$definition->get_prefixed_db_column()]; # for primitives and identifiers just copy the value

			} else if($definition->type_is('custom')){
				$this->load_custom_attribute($definition, $row);

			} else if($definition->class_is(Collection::class)){
				// TODO
				throw new Exception('collections are not yet supported');

			} else if($definition->supclass_is(StaticObject::class)){
				if(empty($row[$definition->get_prefixed_db_column()])){
					continue;
				}

				$cls = $definition->get_class();
				$this->$name = new $cls($this, $definition);
				$this->$name->load($row[$definition->get_prefixed_db_column()]);

			} else if($definition->supclass_is(Entity::class)){
				# check whether an entity was referenced by checking the column referring to the entity
				# that column should contain an id or null, which is from now on stored as $id
				if(empty($row[$definition->get_prefixed_db_column()])){
					# no entity was referenced, set the attribute to null
					$this->$name = null;
					continue;
				}

				# create a new Entity of the defined class and load it
				$cls = $definition->get_class();
				$this->$name = new $cls($this); # set $this as the context entity
				$this->$name->load($row);

			} else if($definition->supclass_is(RelationshipList::class)){
				if(isset($this->context)){ # relationships are disabled (thus set null) on non-independent entities
					$this->$name = null;
					continue;
				}

				# create and set the relationship list and let it load the relationships
				$cls = $definition->get_class();
				$this->$name = new $cls($this); # $this is referenced as context entity
				$this->$name->load($data);

			}
		}

		$this->flow->step('loaded');
		$this->db->set_synced();
	}


	protected function load_custom_attribute(AttributeDefinition $definition, array $row) : void {}


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
			if(!isset(static::$attributes[$name])){ # check if the attribute exists
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
	# protected function edit_custom_attribute(AttributeDefinition $definition, mixed $input) : void;


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

		if($this->db->is_synced()){
			# this entity is not newly created and has not been altered, so do not perform any request
			$request = false;
		} else if($this->db->is_local()){
			# this entity is not yet or not anymore stored in the database, so perform an insert query
			$request = new InsertRequest(static::DB_TABLE);
		} else {
			# this entity is already stored in the database, but has been altered.
			# perform an update query to update its database values
			$request = new UpdateRequest(static::DB_TABLE);
			$request->set_condition(new IdentifierEquals(static::$attributes['id'], $this->id));
		}

		# add the attributes to the request and push the dependencies.
		# the entity attributes this entity contains can be pushed before or after this entity, depending on whether
		# this entity references them (then before) or they reference this entity (then after)
		# naturally, a database record can only be referenced if it already exists
		$push_later = [];
		foreach(static::$attributes as $name => $definition){ // FIXME no side-effects
			if($definition->supclass_is(Entity::class)){
				# single entities are pushed before, so this entity can then reference them in the db
				$this->$name?->push();
			} else if($definition->supclass_is(RelationshipList::class)){
				$push_later[] = $name; # relationship lists are pushed after, as they reference this entity
				continue;
			}

			# if this is local, all attributes are included, otherwise only the alterable ones
			if($request !== false && ($definition->is_alterable() || $this->db->is_local())){
				$request->add_attribute($definition);
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

		foreach($push_later as $name){ # push the attributes that should be pushed later
			$this->$name?->push();
		}

		if($request !== false){ // FIXME this is a hotfix
			$this->push_custom();
		}

		$this->flow->step('stored'); # finish the storing process
		$this->db->set_synced();

		return $request !== false; # return whether a request was performed (for this entity only)
	}


	protected function push_custom() : void {}


	# ---> see trait Attributes
	# final protected function get_push_values() : array;
	# protected function get_custom_push_values() : array;


	# Erase this entity out of the database.
	# this does not delete entities it contains as attributes, but all relationships of this entity will be deleted due
	# to the mysql ON DELETE CASCADE constraint.
	# @return: true if a database request was performed, false if not (i.e. because the entity still/already is local)
	final public function delete() : bool {
		$this->flow->check_step('deleted');

		if($this->db->is_local()){
			# this entity is not yet or not anymore stored in the database, so just return false
			$this->flow->step('deleted');
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

		$this->delete_custom();

		$this->flow->step('deleted');
		$this->db->set_local();

		return true;
	}


	protected function delete_custom() : void {}


	### OUTPUT METHODS

	# ---> see trait Attributes
	# final public function freeze() : void;


	# Transform this entity object into an array (containing all its attributes).
	# attributes that are entities themselves are recursively transformed too (using theír own arrayify functions).
	final public function arrayify() : array|null {
		# if this entity is already in the freezing process, return null. prevents endless loops
		# this also makes sure that this entity only occurs once in the final array, preventing redundancies
		if($this->flow->is_at('freezing')){
			return null;
		}

		$this->flow->step('freezing'); # start the freezing process

		$this->db->disable(); # disable the database access

		$result = [];

		# loop through all attributes and copy them to $result. for entities, copy their arrayified version
		foreach(static::$attributes as $name => $definition){
			if($definition->type_is('entity') || $definition->type_is('object')){
				$result[$name] = $this->$name?->arrayify();
			} else {
				$result[$name] = $this->$name;
			}
		}

		$result = array_merge($result, $this->arrayify_custom()); // TEMP

		$this->flow->step('frozen'); # finish the freezing process

		return $result;
	}


	protected function arrayify_custom() : array {
		return [];
	}


	### GENERAL METHODS

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
