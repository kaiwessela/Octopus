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
	const DB_TABLE = 'images';
	const DB_PREFIX = 'image';

	const LIST_CLASS = ImageList::class;


	public static function get_attribute_definitions() : array {
		return [
			'id' 			=> IDAttribute::define(),
			'longid' 		=> IdentifierAttribute::define(required:true, editable:false),
			'name' 			=> StringAttribute::define(min:1, max:250),
			'description' 	=> StringAttribute::define(min:0, max:250),
			'alternative' 	=> StringAttribute::define(min:0, max:250),
			'copyright' 	=> StringAttribute::define(min:0, max:250),
			'mime_type'		=> ImageMetaAttribute::define(),
			'extension' 	=> ImageMetaAttribute::define(),
			'file' 			=> ImageFileAttribute::define(meta_attributes:['mime_type', 'extension']),
			'variants' 		=> GeneratedAttribute::define()
		];
	}


	protected static function shape_select_request(SelectRequest &$request, $options) : void {
		if(!isset($options['with-file']) || $options['with-file'] == false){
			$request->remove_attribute(static::$attributes['file']);
		}
	}


	protected static function shape_join_request(JoinRequest &$request) : void {
		$request->remove_attribute(static::$attributes['file']);
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
