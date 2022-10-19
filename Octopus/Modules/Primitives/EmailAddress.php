<?php
namespace Octopus\Modules\Primitives;
use Exception;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Database\Requests\Conditions\Equals;
use Octopus\Core\Model\Database\Requests\Conditions\InList;
use Octopus\Core\Model\Database\Requests\Conditions\Condition;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;

class EmailAddress extends PropertyAttribute {


	public static function define(bool $is_required = false, bool $is_editable = true) : EmailAddress {
		$attribute = new static($is_required, $is_editable);

		return $attribute;
	}


	public function load(null|string|int|float $data) : void {
		$this->value = $data;
		$this->is_loaded = true;
	}


	protected function _edit(mixed $input) : void {
		if(empty($input)){
			$this->value = null;
			return;
		}

		if(filter_var($input, \FILTER_VALIDATE_EMAIL) === false){
			throw new IllegalValueException($this, $input, 'not a valid email address');
		}

		$this->value = $input;
	}


	public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_array($option)){
			foreach($option as $opt){
				if(!is_string($opt)){
					throw new Exception('invalid condition.'); // TODO
				}
			}

			return new InList($this, $option);
		} else if(is_string($option)){
			return new Equals($this, $option);
		} else {
			throw new Exception('invalid condition.'); // TODO
		}
	}
}
?>