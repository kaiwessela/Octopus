<?php
namespace Octopus\Modules\Images;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\FileManager\ImageFile;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Modules\Images\ImageList;
use \Octopus\Modules\Images\ImageConfig;
use \Exception;

class Image extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string $name;
	protected ?string $description;
	protected ?string $alternative;
	protected ?string $copyright;
	protected ?string $mime_type;
	protected ?string $extension;
	protected ?array $variants;
	protected ?ImageFile $file;

	protected array $variant_files;

	protected static array $attributes;

	const DB_TABLE = 'images';
	const DB_PREFIX = 'image';

	const LIST_CLASS = ImageList::class;

	final const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => 'name',
		'description' => '.{0,250}',
		'alternative' => '.{0,250}',
		'copyright' => '.{0,250}',
		'mime_type' => [
			'class' => 'custom',
			'alterable' => false,
			'required' => true
		],
		'extension' => [
			'class' => 'custom',
			'alterable' => false,
			'required' => true
		],
		'file' => [
			'class' => 'custom',
			'alterable' => false,
			'required' => true
		],
		'variants' => [
			'class' => 'custom'
		]
	];


	protected static function shape_select_request(SelectRequest &$request, $options) : void {
		if(!isset($options['with-file']) || $options['with-file'] == false){
			$request->remove_attribute(static::$attributes['file']);
		}
	}


	protected static function shape_join_request(JoinRequest &$request) : void {
		$request->remove_attribute(static::$attributes['file']);
	}


	protected function load_custom_attribute(AttributeDefinition $attribute, array $row) : void {
		$column_name = $attribute->get_prefixed_db_column();
		$name = $attribute->get_name();

		if($name === 'file'){
			$this->file = new ImageFile($this->mime_type, $this->extension, $row[$column_name] ?? null);
		} else if($name === 'mime_type' || $name === 'extension'){
			$this->$name = $row[$column_name];
		} else if($name === 'variants'){
			$this->variants = json_decode($row[$column_name], true, 512, \JSON_THROW_ON_ERROR); // TODO
		}
	}


	protected function edit_custom_attribute(AttributeDefinition $attribute, mixed $input) : void {
		if($attribute->get_name() !== 'file'){
			throw new AttributeNotAlterableException($attribute, $this, $this->{$attribute->get_name()});
		} // TODO rewrite this (auto-check alterability and editability)

		// TODO check alterability

		$file = new ImageFile();

		// TEMP from here - $_FILES should be integrated into Controller\Request
		// TODO catch exceptions
		if(isset($_FILES[$attribute->get_name()]['tmp_name'])){
			$file->receive_post($attribute->get_name());
		} else if(!empty($input)){
			$file->receive_base64($input);
		} else {
			throw new Exception('(temp exc.) invalid image input.');
		}
		// temp end

		// TODO check ImageConfig mime type

		$this->file = $file;
		$this->mime_type = $this->file->get_mime_type();
		$this->extension = $this->file->get_extension();
		$this->variants = [];

		$this->db->set_altered();

		$this->variant_files = [];
		// TODO autoversion
	}


	protected function get_custom_push_value(AttributeDefinition $attribute) : mixed {
		$name = $attribute->get_name();

		if($name === 'variants'){
			return json_encode($this->variants, \JSON_THROW_ON_ERROR);
		} else if($name === 'file'){
			return $this->file->get_data();
		} else {
			return $this->$name;
		}
	}


	protected function push_custom() : void {
		$this->write();
	}


	protected function delete_custom() : void {
		$this->erase();
	}


	protected function compute_path(string $variant) : string {
		if(empty(ImageConfig::DIRECTORY) || !is_string(ImageConfig::DIRECTORY)){
			throw new Exception('ImageConfig: DIRECTORY is invalid.');
		}

		$path = ConfigLoader::resolve_path(ImageConfig::DIRECTORY);

		$path = str_replace('{id}', $this->id, $path);
		$path = str_replace('{longid}', $this->longid, $path);
		$path = str_replace('{extension}', $this->extension, $path);
		$path = str_replace('{variant}', $variant, $path);

		$path = preg_replace_callback('/\{(.+)variant\}/', function($matches) use ($variant){
			if($variant === 'original'){
				return '';
			} else {
				return $matches[1] . $variant;
			}
		}, $path);

		return $path;
	}


	protected function write() : void {
		$path = $this->compute_path('original');
		$this->file->write($path, overwrite:true);

		foreach($this->variant_files as $file){
			$path = $this->compute_path($file->variant);
			$file->write($path, overwrite:true);
		}
	}


	protected function erase(?string $variant = null) : void {
		if($variant === null){
			$this->erase('original');

			foreach($this->variants as $variant => $_){
				$this->erase($variant);
			}
		} else {
			$path = $this->compute_path($variant);
			$this->file->erase($path);
		}
	}


	protected function scan() : array {
		// TODO
	}


	public function src(string $variant = 'original', string $url_base = '') : ?string {
		if($variant !== 'original' && !isset($this->variants[$variant])){
			return null;
		}

		$path = $this->compute_path($variant);

		return $url_base . '/' . substr($path, strlen(ConfigLoader::get_document_root()));
	}


	public function srcset() : string {
		// TODO cycle check

		$sources = [];

		foreach($this->variants as $variant){
			$sources[] = $this->src($variant).' '.ImageConfig::SIZES[$variant]['width'].'w';
		}

		return implode(', ', $sources);
	}


	protected function arrayify_custom() : array { // TEMP
		return [
			'src' => $this->src(),
			'srcset' => $this->srcset()
		];
	}
}
?>
