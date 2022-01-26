<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\Collection;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Exception;

# An AttributeDefinition specifies which form an attribute of an entity has, concretely:
#	- what variable type it has,
#	- whether it is required to be set (= not null),
#	- whether it is possible to alter it after creating the entity,
#	- which additional constraints its value has to match in order to be valid,
#	- additional options for custom attributes.
# AttributeDefinitions are used to:
#	- automatically create database requests,
#	- automatically load database values into the entity,
#	- validate the attribute value (especially on user inputs),
#	- export the attribute to upload it to the database,
#	- automatically freeze and arrayify the entity and recursively all entities it contains (as attributes).
#
# AttributeDefinitions also provide additional informations about the attribute, i.e. its database table, prefix and
# column name and the property name the attribute has in its entity.
#
# Attributes are static for each entity class and are automatically generated the first time an entity class is used
# (--> see Attributes trait). The developer of a child class of Entity or Relationship must include a constant named
# ATTRIBUTES inside the class, in which for every attribute, a simplified (so-called raw) definition is provided.
# From these raw definitions, an instance of AttributeDefinition will be generated then.
#
# A raw attribute definition has the following form:
#	property_name => [
#		'class' => class,
#		...options and constraints
#	];
#
# The short form for attributes without options or constraints is:
#	property_name => class;
#
# A special short form for strings with a pattern constraint is:
#	property_name => pattern;
# which is equal to:
#	property_name => [
#		'class' => 'string',
#		'pattern' => pattern
#	];
#
# TYPES AND CLASSES
# The terms class and type should not be mistaken with their meaning in the php documentation. Here, a type is somewhat
# like a category of a class, as described below.
# The following attribute classes and corresponding types exist (currently):
#	CLASS								TYPE
#	id									identifier
#	longid								identifier
#	string								primitive
#	int									primitive
#	float								primitive
#	bool								primitive
#	[any child of StaticObject]			object
#	Collection							object
#	[any child of Entity]				entity
#	[any child of RelationshipList]		entity
#	custom								custom
#	contextual							contextual
# contextual means that the property is not stored in the object itself, but in a relation the object is part of, so
# its value depends on the context object.
# custom types define and check their constraints themselves.
# they are passed on to them with the options array.
#
# CONSTRAINTS AND OPTIONS
# For some attribute classes, it is possible to define addidional constraints an attribute value has to match in order
# to be valid. These constraints are checked when editing the attribute. This check is initiated by the Attributes trait
# and executed by a seperate function of this class (-> validate_input())
#
# The following constraints exist:
# for strings:
#	- pattern: a RegEx the attribute value must match to

class AttributeDefinition {
	private string $name; # the name the property has in the Entity
	private string $type;
	private string $class;
	private bool $required;
	private bool $alterable;
	private array $constraints;
	private ?array $options;
	private string $db_table;
	private string $db_prefix;
	private ?string $db_column;

	# this pattern defines a valid longid
	const LONGID_PATTERN = '/^[a-z0-9-]{9,128}$/';


	# @param $definition: string or array containing a raw property definition
	function __construct(string $name, mixed $definition, string $db_table, string $db_prefix) {
		$this->name = $name;
		$this->db_table = $db_table;
		$this->db_prefix = $db_prefix;

		# check if the raw definition's type is valid
		if(!is_string($definition) && !is_array($definition)){
			throw new Exception('Raw property definition must be a string or an array.');
		}

		# rewrite short form raw definitions into the long form
		if(is_string($definition)){
			$shortcut = $definition; # rename $definition to $shortcut
			$definition = []; # create an empty array for the long-form definition

			if(in_array($shortcut, ['custom', 'id', 'longid', 'string', 'int', 'float', 'bool']) || class_exists($shortcut)){
				# handle normal short form definitions
				$definition['class'] = $shortcut;
			} else {
				# handle the special string-with-pattern short form definition
				$definition['class'] = 'string';
				$definition['pattern'] = $shortcut;
			}
		}

		# complement the type and check if the class is valid
		if($definition['class'] === 'id' || $definition['class'] === 'longid' || $definition['type'] === 'identifier'){
			$this->type = 'identifier';
			$this->class = $definition['class'];
			$this->required = true;
			$this->db_column = $this->name;

			if($this->class === 'id'){
				$this->alterable = false; # ids are never alterable
			} else {
				$this->alterable = $definition['alterable'] ?? true; # other identifiers are alterable by default
			}
		} else if($definition['class'] === 'custom'){
			$this->type = 'custom';
			$this->class = 'custom';
			$this->required = $definition['required'] ?? false;
			$this->alterable = $definition['alterable'] ?? true;
			$this->db_column = $this->name;
		} else if(in_array($definition['class'], ['string', 'int', 'float', 'bool'])){
			$this->type = 'primitive';
			$this->class = $definition['class'];
			$this->required = $definition['required'] ?? false; # primitive properties are not required by default
			$this->alterable = $definition['alterable'] ?? true; # primitive properties are alterable by default
			$this->db_column = $this->name;
		} else if(class_exists($definition['class'])){
			if(is_subclass_of($definition['class'], StaticObject::class)){
				$this->type = 'object';
				$this->required = $definition['required'] ?? false; # static objects are not required by default
				$this->alterable = $definition['alterable'] ?? true; # static objects are alterable by default
				$this->db_column = $this->name;
			} else if($definition['class'] === Collection::class){
				$this->type = 'object';
				$this->required = false; # collections are never required
				$this->alterable = true; # collections are always alterable
				$this->db_column = $this->name;
			} else if(is_subclass_of($definition['class'], Entity::class)){
				$this->type = 'entity';
				$this->required = $definition['required'] ?? false; # entities are not required by default
				$this->alterable = $definition['alterable'] ?? true; # entities are alterable by default
				# remember: this does not affect the alterability of the entity's inner properties
				$this->db_column = "{$this->name}_id";
			} else if(is_subclass_of($definition['class'], RelationshipList::class)){
				$this->type = 'entity';
				$this->required = false; # relationship lists are never required
				$this->alterable = true; # relationship lists are always alterable
				$this->db_column = null;
			} else {
				throw new Exception("Invalid class in raw property definition: «{$definition['class']}».");
			}

			$this->class = $definition['class'];
		} else {
			throw new Exception("Invalid class in raw property definition: «{$definition['class']}».");
		}

		# unset the already processed values, so that in the end, only the options remain in $definition
		unset($definition['type']);
		unset($definition['class']);
		unset($definition['required']);
		unset($definition['alterable']);

		# handle constraints
		$this->constraints = [];

		if($this->class == 'string' && !empty($definition['pattern'])){ # the pattern constraint for strings
			# check if the pattern is a valid RegEx
			if(preg_match("/{$definition['pattern']}/", null) !== false){
				$this->constraints['pattern'] = $definition['pattern'];
			} else {
				throw new Exception('Invalid pattern constraint regex in raw definition: ' . $definition['pattern']);
			}

			unset($definition['pattern']);
		}

		# set the remaining values in $definition as options
		$this->options = $definition;
	}


	public function get_name() : string {
		return $this->name;
	}


	public function get_db_table() : string {
		return $this->db_table;
	}


	public function get_db_prefix() : string {
		return $this->db_prefix;
	}


	public function get_db_column() : string {
		return $this->db_column;
	}


	public function type_is(string $type) : bool {
		return $this->type === $type;
	}


	public function get_type() : string {
		return $this->type;
	}


	public function class_is(string $class) : bool {
		return $this->class === $class;
	}


	public function get_class() : string {
		return $this->class;
	}


	# This function checks whether a given class name refers to a superclass (=parent class) of $this->class
	# using this, it is easy to check whether the defined property is a child of DataType, DataObject and so on
	public function supclass_is(string $class) : bool {
		return ($this->type === 'object' || $this->type === 'entity') && is_subclass_of($this->class, $class);
	}


	public function set_required(bool $value = true) : void {
		$this->required = $value;
	}


	public function set_alterable(bool $value = true) : void {
		$this->alterable = $value;
	}


	public function is_required() : bool {
		return $this->required;
	}


	public function is_alterable() : bool {
		return $this->alterable;
	}


	# This function checks whether an input fulfills all defined constraints
	# if not, it throws a PropertyValueException
	public function validate_input(mixed $input) : void {
		if($this->type_is('identifier') && $this->class_is('longid')){
			# validate a longid
			if(!preg_match(self::LONGID_PATTERN, $input)){
				throw new IllegalValueException($this, $input);
			}

		} else if($this->type_is('primitive') && $this->class_is('string')){
			if(!empty($constraints['pattern'])){ # pattern constraint
				# match the input with the constraint pattern
				if(!preg_match("/{$constraints['pattern']}/", $input)){
					throw new IllegalValueException($this, $input);
				}
			}

		}
	}
}
?>
