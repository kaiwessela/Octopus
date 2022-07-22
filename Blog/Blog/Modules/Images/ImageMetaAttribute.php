<?php
namespace Blog\Modules\Images;
use \Blog\Modules\Images\ImageMetaAttribute;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\FileManager\ImageFile;

class ImageMetaAttribute extends PropertyAttribute {
	protected string $role;


	public static function define(string $role) : ImageMetaAttribute {
		$attribute = new static(true, false);
		$attribute->role = $role; // TODO validate
		return $attribute;
	}


	public function load(null|string|int|float $data) : void {
		if($this->role === 'variants'){
			$this->value = json_decode($data, true, 512, \JSON_THROW_ON_ERROR); // TODO check
		} else {
			$this->value = $data;
		}

		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		if(empty($input)){
			return;
		} else if($input instanceof ImageFile){
			if($this->role === 'mime_type'){
				$this->value = $input->get_mime_type();
			} else if($this->role === 'extension'){
				$this->value = $input->get_extension();
			} else if($this->role === 'variants'){
				$this->value = [];
			}

			$this->is_dirty = true;
		} else {
			throw new AttributeNotAlterableException($this, $this, ''); // TODO
		}
	}


	public function get_push_value() : null|string|int|float {
		if($this->role === 'variants'){
			return json_encode($this->value, \JSON_THROW_ON_ERROR);
		} else {
			return $this->value;
		}
	}
}
?>
