<?php # PropertyDefinition.php 2021-10-04 beta
namespace Blog\Core\Model\Properties;
use \Blog\Core\Model\DataObject;
use \Blog\Core\Model\DataObjectCollection;
use \Blog\Core\Model\DataObjectRelationList;
use \Blog\Core\Model\DataType;
use \Blog\Core\Model\Properties\Exceptions\IllegalValueException;
use Exception;

# The following property classes and corresponding types exist:
#	CLASS										TYPE
#	id											identifier [former special]
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
	private bool $alterable;
	private array $constraints;
	private ?array $options;

	# this pattern defines a valid longid
	const LONGID_PATTERN = '/^[a-z0-9-]{9,128}$/';


	public static function load(array $raw_definitions) : array {
		$result = [];

		foreach($raw_definitions as $name => $options){
			$result[$name] = new PropertyDefinition($name, $options);
		}

		return $result;
	}


	function __construct(string $name, mixed $definition) {
		$this->name = $name;

		# check if the raw definition's type is valid
		if(!is_string($definition) && !is_array($definition)){
			throw new Exception('PropertyDefinition must be a string or an array.');
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

			if($this->class === 'id'){
				$this->alterable = false;
			} else {
				$this->alterable = $definition['alterable'] ?? true;
			}
		} else if($definition['class'] == 'custom'){
			$this->type = 'custom';
			$this->class = 'custom';
		} else if(in_array($definition['class'], ['string', 'int', 'float', 'bool'])){
			$this->type = 'primitive';
			$this->class = $definition['class'];
			$this->alterable = $definition['alterable'] ?? true;
		} else if(class_exists($definition['class'])){
			# check if the object class is allowed for a property
			# it must be a child (=subclass) of DataType, DataObject, D.O.RelationList or D.O.Collection
			if(	is_subclass_of($definition['class'], DataType::class)
			 || is_subclass_of($definition['class'], DataObject::class)
			 || is_subclass_of($definition['class'], DataObjectRelationList::class)
			 || is_subclass_of($definition['class'], DataObjectCollection::class)){
				$this->type = 'object';
				$this->class = $definition['class'];

				if(is_subclass_of($definition['class'], DataObject::class)){
					$this->alterable = $definition['alterable'] ?? true;
				} else {
					$this->alterable = true;
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
		unset($definition['alterable']);

		# handle constraints
		$this->constraints = [];

		if($this->class == 'string' && !empty($definition['pattern']){ # the pattern constraint for strings
			# check if the pattern is a valid RegEx
			if(preg_match('/^'.$definition['pattern'].'$/', null) !== false){ // TODO remove ^ and $
				$this->constraints['pattern'] = $definition['pattern'];
			} else {
				throw new Exception('Invalid pattern constraint RegEx in PropertyDefinition: ' . $definition['pattern']);
			}

			unset($definition['pattern']);
		}

		# set the remaining values in $definition as options
		$this->options = $definition;
	}


	public function type_is(string $type) : bool {
		return $this->type === $type;
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


	public function is_alterable() : bool {
		return $this->alterable;
	}


	# This function checks whether an input fulfills all defined constraints
	# if not, it throws an InputException
	public function validate_input(mixed $input) : void {
		if($this->type_is_identifier() && $this->class === 'longid'){
			# validate a longid
			if(!preg_match(self::LONGID_PATTERN, $input)){
				throw new IllegalValueException($this, $input);
			}

		} else if($this->type_is_primitive() && $this->class === 'string'){
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
