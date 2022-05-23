<?php

// TEMP

namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Exception;

class StaticObjectAttribute extends Attribute {
	protected string $class;


	public static function define(string $class) : StaticObjectAttribute {
		if(!class_exists($class) || !is_subclass_of($class, StaticObject::class)){
			throw new Exception('invalid class.');
		}

		$attr = new StaticObjectAttribute();
		$attr->required = false;
		$attr->editable = true;
		$attr->class = $class;
		return $attr;
	}


	public function load(null|string|int|float $value) : void {
		$this->edited = false;

		if(is_null($value)){
			$this->value = null;
			return;
		}

		$class = $this->get_class();
		$this->value = new $class($this->parent, $this);
		$this->value->load($value);
	}


	public function edit(mixed $input) : void {
		if($input instanceof Attribute){ # dry run; used by StaticObjects’ internal edit methods
			return;
		}

		if(is_null($this->value)){ # if no StaticObject exists yet, create a new one
			$class = $this->get_class();
			$this->value = new $class($this->parent, $this);
		}

		$this->value->edit($input);
		$this->edited = true; // FIXME
	}


	public function get_push_value() : null|string|int|float {
		return $this->value?->export();
	}


	public function get_class() : string {
		return $this->class;
	}
}
?>
