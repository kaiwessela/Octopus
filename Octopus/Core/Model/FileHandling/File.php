<?php
namespace Octopus\Core\Model\FileHandling;
use \Octopus\Core\Model\FileHandling\ApplicationFile;
use \Octopus\Core\Model\FileHandling\AudioFile;
use \Octopus\Core\Model\FileHandling\ImageFile;
use \Octopus\Core\Model\FileHandling\VideoFile;
use \Octopus\Core\Model\FlowControl\Flow;
use finfo;
use Exception;

// TODO explainations

abstract class File {
	public string $name;
	public string $mime_type;
	public string $extension;
	public string $variant;
	public string $data;

	protected Flow $flow;


	function __construct() {
		$this->flow = new Flow([
			['root', 'loaded'],
			['loaded', 'stored'],
			['stored', 'deleted'],
			['deleted', 'stored']
		]);

		$this->flow->start();
	}


	# this static method handles file uploads. it validates the upload data, converts it into a consistent format and
	# returns it as a File object which then can be handled further or stored.
	# there are currently two modes for uploading files:
	# 1. the classic HTML/PHP file upload method, using a standard html form with a file input and reading the data
	# from the $_FILES array.
	# 2. sending the file data as a data url, encoded as base64.
	# by default, receive() does both, first trying the direct/classic approach and, if that fails, base64. this should
	# be the most developer-friendly way, as there is no need to check in which way the user submitted its data.
	# however, it is also possible to only allow one mode, using the $mode parameter.
	# @param $name: the name attribute of the html form file input. can be null on base64-only uploads.
	# @param $b64_data: the base64 encoded string containing the file data, if existing.
	# @param $mode: 'base64' for base64-only mode, 'direct' for classic-only mode, null for auto (default)
	final public static function receive(?string $name = null, ?string $b64_data = null, ?string $mode = null) : File {
		if(isset($_FILES[$name]) && $mode !== 'base64'){
			# classic data is provided and base64-only mode is not forced, so use direct mode
			$mode = 'direct';
		} else if(!is_null($b64_data) && $mode !== 'direct'){
			# base64 data is provided and direct-only mode is not forced, so use base64 mode
			$mode = 'base64';
		} else {
			# no mode is applicable due to wrong usage or input
			throw new Exception('FileManager | Receive » invalid arguments or data provided.');
		}

		if($mode == 'direct'){ # direct mode
			# check if the $_FILES array is formally invalid, i.e. because it is empty or contains multiple files
			if(!isset($_FILES[$name]['error']) || is_array($_FILES[$name]['error'])){
				# invalid $_FILES array (undefined or multiple files or files corruption attack)
				throw new Exception('FileManager | Upload » invalid or unreadable file upload.');
			}

			# check if the file does not exceed the size limit set in the MediaConfig
			if($_FILES[$name]['size'] > MediaConfig::MAX_UPLOAD_SIZE){ // TODO fix this
				throw new Exception('FileManager | Upload » file exceeds config MAX_UPLOAD_SIZE.');
			}

			# if the file upload has an error, throw a matching message
			if($_FILES[$name]['error'] !== UPLOAD_ERR_OK){
				throw new Exception('FileManager ' . match($_FILES[$name]['error']){
					UPLOAD_ERR_INI_SIZE => '| Upload » file exceeds php.ini upload_max_filesize.',
					UPLOAD_ERR_FORM_SIZE => '| Upload » file exceeds form MAX_FILE_SIZE.',
					UPLOAD_ERR_PARTIAL => '| Upload » file was only partially uploaded.',
					UPLOAD_ERR_NO_FILE => '| Upload » no file was uploaded.',
					UPLOAD_ERR_NO_TMP_DIR => '| System » temporary folder missing.',
					UPLOAD_ERR_CANT_WRITE => '| System » failed to write file to disk.',
					UPLOAD_ERR_EXTENSION => '| System » upload stopped by php extension.',
					default => '| Upload » unknown upload error.'
				});
			}

			# read the file's content
			$filedata = file_get_contents($_FILES[$name]['tmp_name']);

			# read the proposed mime type and extension (these cannot be trusted)
			$proposed_mime_type = $_FILES[$name]['type'];
			$proposed_extension = end(explode('.', $_FILES[$name]['name']));

		} else if($mode == 'base64'){ # base64 mode
			# validate base64 data and extract the proposed mime type
			if(preg_match('/^data:(.+);base64,[A-Za-z0-9\+\/]+=*$/', $b64_data, $matches) === false){
				# $matches[1] =   ~~~~		(i.e. image/jpeg)

				throw new Exception('FileManager | Upload » invalid base64 format/encoding.');
			}

			$proposed_mime_type = $matches[1];
			$proposed_extension = null; # proposing an extension is not supported for base64 mode

			# strip the data url header from the base64 input
			$pure_base64 = preg_replace('/^data:(.+);base64,/', '', $b64_data);

			# decode the base64 data
			$filedata = base64_decode($pure_base64, true);

			if($filedata === false){
				# the decoding failed
				throw new Exception('FileManager | Upload » invalid base64 file data.');
			}
		}

		# try to read a mime type from the file data
		$actual_mime_type = mime_content_type($filedata);

		$file = match(true){
			str_starts_with($actual_mime_type, 'application') 	=> new ApplicationFile(),
			str_starts_with($actual_mime_type, 'audio') 		=> new AudioFile(),
			str_starts_with($actual_mime_type, 'image') 		=> new ImageFile(),
			str_starts_with($actual_mime_type, 'video') 		=> new VideoFile(),
			default => throw new Exception() // TODO
		};

		$file->create($filedata, $proposed_mime_type, $proposed_extension);

		return $file;
	}


	final public function create(string $filedata, ?string $proposed_mime_type = null, ?string $proposed_extension = null) : void {
		$this->flow->check_step('loaded');

		# try to read a mime type from the file data
		$actual_mime_type = mime_content_type($filedata);

		# try to find extensions matching to the file's mime type
		$fileinfo = new finfo(FILEINFO_EXTENSION);
		$valid_extensions = explode('/', $fileinfo->file($filedata)); # array of valid extensions

		if($proposed_mime_type !== $actual_mime_type){
			throw new Exception(); // TODO
		}

		$this->mime_type = $actual_mime_type;

		if(!is_null($proposed_extension) && in_array($proposed_extension, $valid_extensions)){
			$this->extension = $proposed_extension;
		} else {
			$this->extension = $valid_extensions[0];
		}

		$this->flow->step('loaded');
	}


	public function write(string $path, bool $overwrite = false) : void {
		$this->flow->check_step('stored');

		self::mkdir(dirname($path));

		if(!is_writable(dirname($path))){
			throw new Exception("File | write » parent directory is not writable: $path.");
		}

		if(file_exists($path) && $overwrite === false){
			throw new Exception("File | write » file already exists: $path.");
		}

		if(file_put_contents($filename, $this->data) === false){
			throw new Exception("File | write » failed to write $path.");
		}

		$this->flow->step('stored');
	}


	final public static function erase(string $path, bool $recursive = false) : void {
		$this->flow->check_step('deleted');

		if(!file_exists($path)){
			throw new Exception("File | erase » file or directory not found: $path.");
		}

		self::rm($path, $recursive);

		if($recursive){ // delete directory if empty
			if(count(scandir(dirname($path))) === 2){
				self::erase(dirname($path, true));
			}
		}

		$this->flow->step('deleted');
	}


	// only single file, no directories
	final public static function scan(string $path) : array {
		if(!file_exists($path)){
			throw new Exception("File | scan » file or directory not found: $path.");
		}

		if(is_dir($path)){
			$result = [];

			foreach(scandir($path) as $child){
				if($child == '.' || $child == '..'){
					continue;
				}

				$result[$child] = self::scan($path . DIRECTORY_SEPATATOR . $child);
			}

			return $result;
		} else return [
			'name' => basename($path);
			'mime_type' => mime_content_type($path);
			'size' => filesize($path);
			'last_edited' => filemtime($path);
		];
	}


	final private static function rm(string $path, bool $recursive = false) : void {
		if(!file_exists($path)){
			throw new Exception("File | rm » file or directory not found: $path.");
		}

		if(!is_writable($path)){
			throw new Exception("File | rm » file or directory not writable: $path.");
		}

		if(is_dir($path)){
			$children = scandir($path);

			if(!empty($children)){
				if($recursive === false){
					throw new Exception("File | rm » directory is not empty: $path.");
				}

				foreach($children as $child){
					if($child === '.' || $child === '..'){
						continue;
					}

					self::rm($path . DIRECTORY_SEPARATOR . $child);
				}
			}

			if(!rmdir($path)){
				throw new Exception("File | rm » failed to remove directory $path.");
			}
		} else {
			if(!unlink($path)){
				throw new Exception("File | rm » failed to remove file $path.");
			}
		}
	}


	final private static function mkdir(string $path) : void {
		if(file_exists($path)){
			if(!is_dir($path)){
				throw new Exception("File | mkdir » $path exists but is not a directory.");
			}

			return;
		}

		if(!mkdir($path, recursive:true)){
			throw new Exception("File | mkdir » failed to create directory $path.");
		}
	}
}
?>
