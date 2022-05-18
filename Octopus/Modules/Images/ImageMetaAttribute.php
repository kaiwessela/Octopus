<?php
namespace Octopus\Modules\Images;
use \Octopus\Modules\Images\ImageAttribute;
use \Octopus\Core\Model\Attributes\Attribute;

class ImageMetaAttribute extends Attribute {
	protected string $field;


	public static function define(string $field) : ImageMetaAttribute {
		if(!in_array($field, ['mime_type', 'extension'])){
			throw new Exception(); // TODO
		}

		$this->required = true;
		$this->editable = true;
	}


	public function edit(mixed $input) : void {
		if(!$input instanceof ImageAttribute){
			throw new Exception(); // TODO
		}

		if($this->field === 'mime_type'){
			$this->value = $input->get_value()->get_mime_type();
		} else if($this->field === 'extension'){
			$this->value = $input->get_value()->get_extension();
		}
	}
}
?>
