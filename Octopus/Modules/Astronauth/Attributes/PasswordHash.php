<?php
namespace Octopus\Modules\Astronauth\Attributes;
use Octopus\Core\Model\Attributes\PropertyAttribute;
use Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;

class PasswordHash extends PropertyAttribute {
	

	public static function define(bool $is_required = true, bool $is_editable = true) : PasswordHash {
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

		if(!is_string($input)){
			throw new IllegalValueException($this, $input, 'not a string');
		}

		$this->value = password_hash($input, \PASSWORD_DEFAULT);
	}


	public function verify(mixed $password) : bool {
		$this->require_loaded();

		if(!is_string($password)){
			throw new IllegalValueException($this, '', 'not a string');
		}

		return password_verify($password, $this->value);
	}


	public function needs_rehash() : bool {
		$this->require_loaded();

		return password_needs_rehash($this->value, $this->value);
	}
}
?>