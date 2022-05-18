<?php
namespace Octopus\Modules\Images;

class ImageFileAttribute extends Attribute {
	# inherited from Attribute:
	# protected Entity $parent;
	# protected string $name;
	# protected ?string $db_column;
	# protected mixed $value;
	# protected bool $editable;
	# protected bool $required;

	protected array $meta_attributes;


	public static function define(bool $required = false, bool $editable = false, array $meta_attributes = []) : ImageFileAttribute {
		$this->required = $required;
		$this->editable = $editable;

		$this->meta_attributes = $meta_attributes; // TODO check this
	}


	public function load(mixed $value) : void {
		$this->value = new ImageFile($this->parent->mime_type, $this->parent->extension, $value);
	}


	public function edit(mixed $input) : void { // TEMP
		// TODO editability

		$file = new ImageFile();

		if(isset($_FILES[$this->name]['tmp_name'])){
			$file->receive_post($this->name);
		} else if(!empty($input)){
			$file->receive_base64($input);
		} else {
			throw new Exception('temp exc. invalid image input');
		}

		$this->value = $file;

		foreach($this->meta_attributes as $name){
			$this->parent->edit_attribute($name, $this);
		}

		// TODO variants, autoversion ...
	}


	protected function return_push_value() : null|string|int|float {
		return $this->value->get_data();
	}


	protected function export() : null|string|int|float|bool|array {

	}


	public function store() : void {

	}


	public function erase() : void {

	}
}
?>
