<?php # Medium.php 2021-10-04 beta
namespace Blog\Modules\Media;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\Media\Application;
use \Blog\Modules\Media\Audio;
use \Blog\Modules\Media\Image;
use \Blog\Modules\Media\Video;

// not ideal from here
use \Blog\Config\Config;
use \Blog\Config\MediaConfig;
use Exception;

abstract class Medium extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected ?string 	$name;
	protected ?string 	$description;
	protected ?string 	$alternative;
	protected ?string 	$copyright;
	protected string 	$mime_type;
	protected string 	$extension;
	protected ?array 	$variants; // TODO check structure and ?

	protected ?File $file;
	protected ?array $variant_files;


	const PROPERTIES = [ // DEPRECATED
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{0,140}',
		'description' => '.{0,250}',
		'alternative' => '.{0,250}',
		'copyright' => '.{0,250}',
		'file' => 'custom',
		'type' => 'custom',
		'extension' => 'custom',
		'variants' => 'custom'
	];


	const DB_PREFIX = 'medium';


	abstract protected function autoversion() : void;


	public function pull_file() : void {
		// TODO cycle check

		if($this->db->is_local()){
			throw new Exception(); // TODO -- no file to pull
		}


	}


	protected function load_custom_property(string $name, mixed $value, ?PropertyDefinition $def = null) : void {
		if($name === 'variants'){
			json_decode($value, true, default, \JSON_THROW_ON_ERROR);
		}
	}


	protected function edit_custom_property(string $name, mixed $input, ?PropertyDefinition $def = null) : void {
		if($name !== 'file' || !$this->db->is_local()){
			return;
		}

		# $name === 'file' && $this->db->is_local()

		$file = File::receive('file', $input);
		$file->variant = 'original';

		if($file::class !== $this::FILE_CLASS){
			throw new Exception();
		}

		$allowed_mime_types = Config::get('Modules.'.$this::class.'.allowed_mime_types', 'array');
		if(!in_array($file->mime_type, $allowed_mime_types)){
			throw new Exception();
		}

		$this->file = $file;
		$this->mime_type = $file->mime_type;
		$this->extension = $file->extension;
		$this->files['original'] = $file;
		$this->variants = [];

		$this->autoversion();
	}


	protected function get_custom_push_values(string $property) : array {
		if($property === 'file'){
			return $this->files['original']->data; // TODO fix this for null etc.
		}
	}


	protected function push_custom_after() : void {
		$this->write();
	}

	protected function delete_custom() : void {
		$this->erase();
	}


	protected function write() : void {
		$path = $this->compute_path();
		$this->file->write($path, overwrite:true);

		foreach($variant_files as $file){
			$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->compute_path($file->variant);
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
			$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->compute_path($variant);
			File::erase($path, recursive:true);
		}
	}

	protected function scan() : array {

	}

	protected function compute_path(string $variant = 'original') : string {
		$path = trim(Config::get('Modules.'.$this::class.'.directory', 'string'), DIRECTORY_SEPARATOR);

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


	public function src(string $variant = 'original') : ?string {
		// TODO cycle check

		if($variant !== 'original' && !isset($this->variants[$variant])){
			return null;
		}

		return /* TODO CURRENT_BASE_URL */ . DIRECTORY_SEPARATOR . $this->compute_path($variant);
	}



	const QUERY_PULL_LEAD = <<<SQL
SELECT (medium_id, medium_longid, medium_class, medium_mime_type, medium_extension, medium_title, medium_description,
medium_alternative, medium_copyright, medium_variants) FROM media
SQL;

	const QUERY_JOIN = <<<SQL
LEFT JOIN media ()
SQL;




	const INSERT_QUERY = <<<SQL
INSERT INTO media
(medium_id, medium_longid, medium_class, medium_type, medium_extension, medium_title, medium_description, medium_copyright,
medium_alternative, medium_variants)
VALUES (:id, :longid, :class, :type, :extension, :title, :description, :copyright, :alternative, :variants)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE media SET
	medium_title = :title,
	medium_description = :description,
	medium_copyright = :copyright,
	medium_alternative = :alternative,
	medium_variants = :variants
WHERE medium_id = :id
SQL; #---|

	const DELETE_QUERY = <<<SQL
DELETE FROM media
WHERE medium_id = :id
SQL; #---|

}
?>
