<?php
namespace Octopus\Core\Model\Attributes;

class FileAttribute extends Attribute {
	# inherited from Attribute:
	# protected Entity $parent;
	# protected string $name;
	# protected ?string $db_column;
	# protected mixed $value;
	# protected bool $editable;
	# protected bool $required;


	public static function define(string $class, bool $required = false, bool $editable = false) : FileAttribute {

	}


	public function load(mixed $value) : void {
		if(empty($value)){
			$this->value = null;
		} else {
			$cls = $this->class;
			$this->value = new $cls();
			$this->value->load($value);
		}
	}
}
?>
