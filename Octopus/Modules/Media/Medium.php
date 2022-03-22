<?php
namespace Octopus\Modules\Media;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\FileHandling\File;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeNotAlterableException;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Modules\Media\Application;
use \Octopus\Modules\Media\Audio;
use \Octopus\Modules\Media\Image;
use \Octopus\Modules\Media\Video;


abstract class Medium extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 	$name;
	protected ?string 	$description;
	protected ?string 	$alternative;
	protected ?string 	$copyright;
	protected ?string 	$mime_type;
	protected ?string 	$extension;
	protected ?array 	$variants;
	protected ?File 	$file;

	protected ?array $variant_files;

	protected static array $attributes;

	const DB_TABLE = 'media';
	const DB_PREFIX = 'medium';

	final const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{0,140}',
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


	abstract protected function autoversion() : void;


	protected static function shape_select_request(SelectRequest &$request, $options) : void {
		if(($options['include_file'] ?? false) != true){
			$request->remove_attribute(static::$attributes['file']);
		}
	}

	protected static function shape_join_request(JoinRequest &$request) : void {
		$request->remove_attribute(static::$attributes['file']);
	}


	protected function load_custom_attribute(AttributeDefinition $definition, array $row) : void {
		$column_name = "{$definition->get_db_prefix()}_{$definition->get_db_column()}";

		if($definition->get_name() === 'file'){
			if(isset($row[$column_name])){
				$cls = static::FILE_CLASS;
				$this->file = new $cls();
				$this->file->create($row[$column_name], $this->mime_type, $this->extension);
			}
		} else if($definition->get_name() === 'mime_type' || $definition->get_name === 'extension'){
			$this->{$definition->get_name()} = $row[$column_name];
		} else if($definition->get_name() === 'variants'){
			$this->variants = json_decode($row[$column_name], true, 512, \JSON_THROW_ON_ERROR); // TODO
		}
	}


	protected function edit_custom_attribute(AttributeDefinition $definition, mixed $input) : void {
		if($definition->get_name() !== 'file' || !$this->db->is_local()){
			throw new AttributeNotAlterableException($definition, $this, $this->{$definition->get_name()});
		}

		$file = File::receive('file', $input);
		$file->variant = 'original';

		if($file::class !== static::FILE_CLASS){
			throw new IllegalValueException($definition, $file, 'wrong file class');
		}

		if(!in_array($file->mime_type, $this::CONFIG::ALLOWED_MIME_TYPES)){
			throw new IllegalValueException($definition, $file, 'illegal mime type');
		}

		$this->file = $file;
		$this->mime_type = $file->mime_type;
		$this->extension = $file->extension;
		$this->variants = [];

		$this->db->set_altered();

		$this->autoversion();
	}


	protected function get_custom_push_value(AttributeDefinition $definition) : array {
		if($definition->get_name() === 'variants'){
			return json_encode($this->variants, \JSON_THROW_ON_ERROR);
		} else {
			return $this->{$definition->get_name()};
		}
	}


	protected function push_custom() : void {
		$this->write();
	}


	protected function delete_custom() : void {
		$this->erase();
	}


	final protected function compute_path(string $variant) : string {
		if(empty($this::CONFIG::DIRECTORY) || !is_string($this::CONFIG::DIRECTORY)){
			throw new Exception('Module config: DIRECTORY is not set or not a string.');
		}

		$path = ConfigLoader::resolve_path($this::CONFIG::DIRECTORY);

		$path = str_replace('{id}', $this->id, $path);
		$path = str_replace('{longid}', $this->longid, $path);
		$path = str_replace('{extension}', $this->extension, $path);
		$path = str_replace('{variant}', $variant, $path);

		$path = preg_replace_callback('/\{(.+)variant\}/', function($matches){
			if($variant !== 'original'){
				return '';
			} else {
				return $matches[1] . $variant;
			}
		}, $path);

		return $path;
	}


	final protected function write() : void {
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
			File::erase($path, recursive:true);
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

		return $url_base . substr($path, strlen(ConfigLoader::get_document_root()));
	}
}
?>
