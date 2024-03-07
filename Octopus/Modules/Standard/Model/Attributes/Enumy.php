<?php
namespace Octopus\Modules\Standard\Model\Attributes;
use Exception;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use Octopus\Core\Model\Attributes\PropertyAttribute;

class Enumy extends PropertyAttribute {
	protected string $class;


	public static function define(string $class, bool $is_required = false, bool $is_editable = true) : Enumy {
		if(!enum_exists($class)){
			throw new Exception("Invalid class «{$class}». Not found or not an enum.");
		}
		
		$attribute = new static($is_required, $is_editable);
		$attribute->class = $class;

		return $attribute;
	}


	public function load(null|string|int|float $data) : void {
		if(is_null($data)){
			$this->value = null;
		} else {
			try {
				$class = $this->class;
				$this->value = $class::from($data);
			} catch(Exception $e){
				throw new Exception('Database value corrupted.');
			}
		}

		$this->is_loaded = true;
	}


	public function _edit(mixed $input) : void {
		if(empty($input)){
			$this->value = null;
			return;
		}

		if(is_string($input) || is_int($input)){
			try {
				$class = $this->class;
				$this->value = $class::from($input);
			} catch(Exception $e){
				throw new IllegalValueException($this, $input, 'cannot convert value');
			}
		} else if($input instanceof $this->class){
			$this->value = $input;
		} else {
			throw new IllegalValueException($this, $input, 'not an int, string or enum');
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value->value;
	}


	public function arrayify() : null|string|int|float|bool|array {
		return $this->value->value;
	}
}
?>
