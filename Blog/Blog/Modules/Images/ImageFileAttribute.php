<?php
namespace Blog\Modules\Images;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\FileManager\ImageFile;

class ImageFileAttribute extends PropertyAttribute {
	protected array $meta_attributes;


	public static function define(array $meta = []) : ImageFileAttribute {
		$attribute = new static(true, false);
		$attribute->meta_attributes = $meta; // TODO validate
		return $attribute;
	}


	public function load(null|string|int|float $data) : void {
		$mime_type_attr = $this->meta_attributes['mime_type'];
		$extension_attr = $this->meta_attributes['extension'];

		$mime_type = $this->parent->$mime_type_attr;
		$extension = $this->parent->$extension_attr;

		$this->value = new ImageFile($mime_type, $extension, $data);
		$this->is_loaded = true;
	}


	public function edit(mixed $input) : void {
		if(!empty($input) && !$this->is_editable()){
			throw new AttributeNotAlterableException($this, $this, 'image'); // TODO
		}

		$file = new ImageFile();

		if(is_array($input)){
			$file->receive_post($this->get_name());
		} else {
			$file->receive_base64($input);
		}

		$this->value = $file;
		$this->is_dirty = true;

		foreach($this->meta_attributes as $role => $name){
			$this->parent->edit_attribute($this->value);
		}
	}


	public function get_push_value() : null|string|int|float {
		return $this->value->get_data();
	}
}
?>
