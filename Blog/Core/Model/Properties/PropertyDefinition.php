<?php
namespace Octopus\Core\Model\Properties;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectCollection;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use \Octopus\Core\Model\Properties\Exceptions\IllegalValueException;
use Exception;

# The following property classes and corresponding types exist (currently):
#	CLASS										TYPE
#	id											identifier
#	longid										identifier
#	string										primitive
#	int											primitive
#	float										primitive
#	bool										primitive
#	[any child of DataType]						object
#	[any child of DataObject]					object
#	[any child of DataObjectRelationList]		object
#	DataObjectCollection						object
#	custom										custom
#	contextual									contextual

# contextual means that the property is not stored in the object itself, but in a relation the object is part of, so
# its value depends on the context object

# The following constraints exist:
# CLASS==string
#	- pattern: a RegEx the property value must match to
#
# objects and custom types define and check their constraints themselves.
# they are passed on to them with the options array.

# A 'raw' property definition has the following form:
#	property_name => [
#		'class' => class_name,
#		...options and constraints
#	];
#
# The short form for properties without options or constraints is:
#	property_name => class_name;
#
# A special short form for strings with a pattern constraint is:
#	property_name => pattern;
# which means the same as:
#	property_name => [
#		'class' => 'string',
#		'pattern' => pattern
#	];


class PropertyDefinition {
	private string $name; # the name of the property
	private string $type;
	private string $class;
	private bool $required;
	private bool $alterable;
	private array $constraints;
	private ?array $options;

	# this pattern defines a valid longid
	const LONGID_PATTERN = '/^[a-z0-9-]{9,128}$/';


	# @param $definition: string or array containing a raw property definition
	function __construct(string $name, mixed $definition) {
		$this->name = $name;

		# check if the raw definition's type is valid
		if(!is_string($definition) && !is_array($definition)){
			throw new Exception('raw property definition must be a string or an array.');
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
		if($definition['class'] == 'id' || $definition['class'] == 'longid'){
			$this->type = 'identifier';
			$this->class = $definition['class'];
			$this->required = true;

			if($this->class === 'id'){
				$this->alterable = false; # ids are never alterable
			} else {
				$this->alterable = $definition['alterable'] ?? true; # other identifiers are alterable by default
			}
		} else if($definition['class'] == 'custom'){
			$this->type = 'custom';
			$this->class = 'custom';
		} else if(in_array($definition['class'], ['string', 'int', 'float', 'bool'])){
			$this->type = 'primitive';
			$this->class = $definition['class'];
			$this->required = $definition['required'] ?? false; # primitive properties are not required by default
			$this->alterable = $definition['alterable'] ?? true; # primitive properties are alterable by default
		} else if(class_exists($definition['class'])){
			# check if the object class is allowed for a property
			# it must be a child (=subclass) of DataType, DataObject, D.O.RelationList or D.O.Collection
			if(	is_subclass_of($definition['class'], DataType::class)
			 || is_subclass_of($definition['class'], DataObject::class)
			 || is_subclass_of($definition['class'], DataObjectRelationList::class)
			 || is_subclass_of($definition['class'], DataObjectCollection::class)){
				$this->type = 'object';
				$this->class = $definition['class'];

				if(is_subclass_of($definition['class'], DataType::class)){
					$this->required = $definition['required'] ?? false; # DataTypes are not required by default
					$this->alterable = true; # DataTypes are always alterable
				} else if(is_subclass_of($definition['class'], DataObject::class)){
					$this->required = $definition['required'] ?? false; # DataObjects are not required by default
					$this->alterable = $definition['alterable'] ?? true; # DataObjects are alterable by default
					# NOTE: this does not affect the alterability of the object's properties
				} else {
					$this->required = false; # RelationLists and Collections are never required
					$this->alterable = true; # RelationLists and Collections are always alterable
				}
			} else {
				throw new Exception('Invalid class in PropertyDefinition: ' . $definition['class']);
			}
		} else {
			throw new Exception('Invalid class in PropertyDefinition: ' . $definition['class']);
		}

		# unset the already processed values, so that in the end, only the options remain in $definition
		unset($definition['type']);
		unset($definition['class']);
		unset($definition['required']);
		unset($definition['alterable']);

		# handle constraints
		$this->constraints = [];

		if($this->class == 'string' && !empty($definition['pattern']){ # the pattern constraint for strings
			# check if the pattern is a valid RegEx
			if(preg_match('/^'.$definition['pattern'].'$/', null) !== false){ // TODO remove ^ and $
				$this->constraints['pattern'] = $definition['pattern'];
			} else {
				throw new Exception('Invalid pattern constraint RegEx in raw definition: ' . $definition['pattern']);
			}

			unset($definition['pattern']);
		}

		# set the remaining values in $definition as options
		$this->options = $definition;
	}


	public function get_name() : string {
		return $this->name;
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
		return $this->type === 'object' && is_subclass_of($this->class, $class);
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
				if(!preg_match('/^'.$constraints['pattern'].'$/', $input)){
					throw new IllegalValueException($this, $input);
				}
			}

		}
	}
}
?>
