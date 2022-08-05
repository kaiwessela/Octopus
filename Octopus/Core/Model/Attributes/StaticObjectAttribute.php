<?php
namespace Octopus\Core\Model\Attributes;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\StaticObject;
use \Exception;

abstract class StaticObjectAttribute extends PropertyAttribute {
	protected string $class;
	protected StaticObject $prototype;

	protected const OBJECT_CLASS = null;


	public static function define(bool $is_required = false, bool $is_editable = true) : StaticObjectAttribute {
		if(!class_exists(static::OBJECT_CLASS) || !is_subclass_of(static::OBJECT_CLASS, StaticObject::class)){
			throw new Exception('invalid class.');
		}

		$attribute = new static($is_required, $is_editable);
		$attribute->class = static::OBJECT_CLASS;

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
		$this->set_dirty(); // FIXME
	}


	public function get_push_value() : null|string|int|float {
		return $this->value?->export();
	}


	public function get_class() : string {
		return $this->class;
	}


	final public function arrayify() : null|string|int|float|bool|array {
		return $this->value?->arrayify();
	}


	final public function get_prototype() : StaticObject {
		if(!isset($this->prototype)){
			$class = $this->get_class();
			$this->prototype = new $class($this->parent, $this);
		}

		return $this->prototype;
	}
}
?>
