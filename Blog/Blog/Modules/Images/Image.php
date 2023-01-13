<?php
namespace Blog\Modules\Images;
use Blog\Modules\Images\ImageFileAttribute;
use Blog\Modules\Images\ImageList;
use Blog\Modules\Images\ImageMetaAttribute;
use Octopus\Core\Model\Entity;
use Octopus\Modules\Identifiers\ID;
use Octopus\Modules\Identifiers\StringIdentifier;
use Octopus\Modules\Standard\Model\Attributes\Strng;

class Image extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Strng $name;
	protected Strng $description;
	protected Strng $alternative;
	protected Strng $copyright;
	protected ImageMetaAttribute $mime_type;
	protected ImageMetaAttribute $extension;
	protected ImageMetaAttribute $variants;
	protected ImageFileAttribute $file;

	protected const DB_TABLE = 'images';
	protected const LIST_CLASS = ImageList::class;


	protected static function define_attributes() : array {
		return [
			'id' 			=> ID::define(),
			'longid' 		=> StringIdentifier::define(is_editable:false),
			'name' 			=> Strng::define(min:1, max:100),
			'description' 	=> Strng::define(min:0, max:250),
			'alternative' 	=> Strng::define(min:0, max:250),
			'copyright' 	=> Strng::define(min:0, max:250),
			// 'mime_type' 	=> ImageMetaAttribute::define(role:'mime_type'),
			// 'extension' 	=> ImageMetaAttribute::define(role:'extension'),
			// 'variants' 		=> ImageMetaAttribute::define(role:'variants'),
			// 'file' 			=> ImageFileAttribute::define(meta:[
			// 	'mime_type' => 'mime_type',
			// 	'extension' => 'extension',
			// 	'variants' => 'variants',
			// ]),
		];
	}


	protected const PRIMARY_IDENTIFIER = 'id';


	protected const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'longid' => true,
		'name' => true,
		'description' => true,
		'alternative' => true,
		'copyright' => true,
		'mime_type' => true,
		'extension' => true,
		'variants' => true,
		'file' => false
	];


	// IDEA
	// protected function init() : void {
	// 	$this->file->add_event_listener(AttributeEditEvent::class, function($event) use ($this){
	// 		$this->mime_type->edit($event->get_attribute()->get_value()->get_mime_type());
	// 		$this->extension->edit($event->get_attribute()->get_value()->get_extension());
	// 		$this->variants->edit($event->get_attribute()->get_value()->get_variants());
	// 	});
	// }


	public function src(string $variant = 'original', string $url_base = '') : string {
		return ''; // TODO
	}


	public function srcset() : string {
		return ''; // TODO
	}


}
