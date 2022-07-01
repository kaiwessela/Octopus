<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\StaticObject;
use \Exception;

class StaticObjectAttribute extends PropertyAttribute {
	protected string $class;


	public static function define(string $class) : StaticObjectAttribute {
		if(!class_exists($class) || !is_subclass_of($class, StaticObject::class)){
			throw new Exception('invalid class.');
		}

		$attribute = new static(false, true);
		$attribute->class = $class;

		return $attribute;
	}


	public function load(null|string|int|float $data) : void {
		if(is_null($data)){
			$this->value = null;
		} else {
			$class = $this->get_class();
			$this->value = new $class($this->parent, $this);
			$this->value->load($data);
		}

		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		if($input instanceof PropertyAttribute){ # dry run; used by StaticObjects’ internal edit methods
			return;
		}

		if(is_null($this->value)){ # if no StaticObject exists yet, create a new one
			$class = $this->get_class();
			$this->value = new $class($this->parent, $this);
		}

		$this->value->edit($input);
		$this->is_dirty = true; // FIXME
	}


	public function get_push_value() : null|string|int|float {
		return $this->value?->export();
	}


	public function get_class() : string {
		return $this->class;
	}


	final public function arrayify() : null|string|int|float|bool|array {
		return $this->value->arrayify();
	}
}
?>
