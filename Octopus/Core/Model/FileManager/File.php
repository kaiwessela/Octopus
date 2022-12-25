<?php




// CURRENTLY NOT WORKING







namespace Octopus\Core\Model\FileManager;
use \Octopus\Core\Model\FlowControl\Flow;
use \Exception;

class File {
	protected ?string $mime_type;
	protected ?string $extension;
	protected mixed $data; # string

	protected Flow $flow;


	function __construct(?string $mime_type = null, ?string $extension = null, ?string $data = null) {
		if(is_null($mime_type) !== is_null($extension)){
			throw new Exception('File » construct: mime_type and extension must be either both set or both null.');
		} else if(is_null($mime_type) && !is_null($data)){
			throw new Exception('File » construct: data must be null too if mime_type and extension are null.');
		}

		// IDEA maybe check mime type and extension

		$this->mime_type = $mime_type;
		$this->extension = $extension;
		$this->data = $data;

		$this->flow = new Flow([
			['root', 'constructed'],
			['root', 'metaloaded'],
			['root', 'dataloaded'],
			['constructed', 'dataloaded'],
			['dataloaded', 'edited'],
			['edited', 'edited']
		]);

		if(is_null($this->mime_type)){
			$this->flow->start('constructed');
		} else if(is_null($this->data)){
			$this->flow->start('metaloaded');
		} else {
			$this->flow->start('dataloaded');
		}
	}


	# receiving the file data as a data url, encoded as base64.
	public function receive_base64(string $base64_data) : void {
		$this->flow->check_step('dataloaded');

		# validate base64 data and extract the proposed mime type
		if(preg_match('/^data:(.+);base64,[A-Za-z0-9\+\/]+=*$/', $base64_data, $matches) === false){
			# $matches[1] =   ~~~~	(i.e. image/jpeg)
			throw new Exception('File » Upload: could not recognize data as base64. invalid format or encoding.');
		}

		$proposed_mime_type = $matches[1];

		# strip the data url header from the base64 input
		$base64_content = preg_replace('/^data:(.+);base64,/', '', $base64_data);

		# decode the base64 content
		$filedata = base64_decode($base64_content, true);

		if(!$filedata){
			throw new Exception('File » Upload: could not decode base64 data.');
		}

		$this->handle_received($filedata, $proposed_mime_type);
	}


	# the classic HTML/PHP file upload method, using a standard html form with a file input and reading the data
	# from the $_FILES array.
	public function receive_post(string $fieldname) : void {
		$this->flow->check_step('dataloaded');

		# check if the $_FILES array is formally invalid, i.e. because it is empty or contains multiple files
		if(!isset($_FILES[$fieldname]['error']) || is_array($_FILES[$fieldname]['error'])){
			throw new Exception('File » Upload: invalid or unreadable file upload.');
		}

		# if the file upload has an error, throw a matching message
		if($_FILES[$fieldname]['error'] !== UPLOAD_ERR_OK){
			throw new Exception('File » Upload ' . match($_FILES[$fieldname]['error']){
				UPLOAD_ERR_INI_SIZE 	=> 'file size exceeds maximum upload size (see php.ini).',
				UPLOAD_ERR_FORM_SIZE 	=> 'file size exceeds MAX_FILE_SIZE value set by the html form.',
				UPLOAD_ERR_PARTIAL 		=> 'file was only partially uploaded.',
				UPLOAD_ERR_NO_FILE 		=> 'no file was uploaded.',
				UPLOAD_ERR_NO_TMP_DIR 	=> 'no temporary file directory set/found.',
				UPLOAD_ERR_CANT_WRITE 	=> 'failed to write file into temporary file directory.',
				UPLOAD_ERR_EXTENSION 	=> 'upload stopped by a php extension.',
				default => 'unknown upload error.'
			});
		}

		# read the file's content
		$filedata = file_get_contents($_FILES[$fieldname]['tmp_name']);

		# read the proposed mime type and extension (these obviously cannot be trusted)
		$proposed_mime_type = $_FILES[$fieldname]['type'];
		$proposed_extension = end(explode('.', $_FILES[$fieldname]['name']));

		$this->handle_received($filedata, $proposed_mime_type, $proposed_extension);
	}


	protected function handle_received(string $filedata, ?string $proposed_mime_type = null, ?string $proposed_extension = null) : void {
		$this->flow->check_step('dataloaded');

		$mimeinfo = finfo_open(FILEINFO_MIME_TYPE);
		$extninfo = finfo_open(FILEINFO_EXTENSION);

		$actual_mime_type = $mimeinfo->buffer($filedata);
		$valid_extension_str = $extninfo->buffer($filedata);

		if($valid_extension_str === '???' || $valid_extension_str === false){
			throw new Exception('File » Upload: could not find a valid extension.');
		}

		$valid_extensions = explode('/', $valid_extension_str);

		if(!is_null($proposed_mime_type) && $proposed_mime_type !== $actual_mime_type){
			throw new Exception("File » Upload: proposed mime type «{$proposed_mime_type}» does not match actual mime type «{$actual_mime_type}».");
		}

		$this->mime_type = $actual_mime_type;

		if(!is_null($proposed_extension) && in_array($proposed_extension, $valid_extensions)){
			$this->extension = $proposed_extension;
		} else {
			$this->extension = $valid_extensions[0];
		}

		$this->data = $filedata;

		$this->flow->step('dataloaded');
	}


	final public function write(string $path, bool $overwrite = true) : void {
		$this->flow->check_step('edited');

		$directory = dirname($path);

		self::mkdir($directory, recursive:true);

		if(!is_writable($directory)){
			throw new Exception("File » write: not allowed to write into parent directory: «{$directory}».");
		}

		if(file_exists($path) && $overwrite === false){
			throw new Exception("File » write: a file with that name already exists: «{$path}».");
		}

		if(!file_put_contents($path, $this->get_data())){
			throw new Exception("File » write: failed to write: «{$path}».");
		}

		chmod($path, 0o770);

		$this->flow->step('edited');
	}


	final public function erase(string $path) : void {
		$this->flow->check_step('edited');

		static::rm($path);

		// TODO remove directory

		$this->flow->step('edited');
	}


	public function scan(string $path) : ?array {
		if(!file_exists($path)){
			return null;
		} else {
			return [
				'mime_type' => mime_content_type($path),
				'size' => filesize($path),
				'last_edited' => filemtime($path)
			];
		}
	}


	public function get_mime_type() : ?string {
		return $this->mime_type;
	}


	public function get_extension() : ?string {
		return $this->extension;
	}


	public function get_data() : ?string {
		return $this->data;
	}


	# remove a file or directory (similar to "rm -f" in the command line)
	# @param $path: the path pointing to the file or directory.
	# @param $recursive: whether to also remove all directories and files inside the directory.
	private static function rm(string $path, bool $recursive = true) : void {
		if(!file_exists($path)){ # check whether the file/directory exists
			throw new Exception("File » rm: file or directory not found: «{$path}».");
		}

		if(!is_writable($path)){ # check whether the script has permission to edit file/directory
			throw new Exception("File » rm: file or directory not writable: «{$path}».");
		}

		if(is_dir($path)){ # if $path points to a directory
			$children = scandir($path); # scan the directory for its contents

			if(!empty($children)){ # true if there are children
				if($recursive === false){ # if children should not be removed, the directory cannot be removed
					throw new Exception("File » rm: directory is not empty: «{$path}».");
				}

				foreach($children as $child){
					if($child === '.' || $child === '..'){ # skip the parent and the directory itself
						continue;
					}

					self::rm($path . DIRECTORY_SEPARATOR . $child); # recursively remove the child file/directory
				}
			}

			if(!rmdir($path)){ # remove the directory itself
				throw new Exception("File » rm: failed to remove directory «{$path}».");
			}
		} else { # if $path points to a file
			if(!unlink($path)){ # simply remove the file
				throw new Exception("File » rm: failed to remove file «{$path}».");
			}
		}
	}


	# create a new directory (similar to "mkdir" in the command line)
	# the directory will have the owner and group of the user executing this script (usually the webserver user,
	# i.e. http or www-data) and have full permissions for owner and group and none for others (chmod 770)
	# @param $path: the path of the new directory.
	# @param $recursive: whether to automatically create all parent directories if they dont exist yet.
	private static function mkdir(string $path, bool $recursive = true) : void {
		if(file_exists($path)){ # check whether $path points to an existing file/directory
			if(!is_dir($path)){ # if $path points to a file, throw an error
				throw new Exception("File » mkdir: «{$path}» exists but is not a directory.");
			}

			return; # simply return if the directory already exists
		}

		$parent = dirname($path);
		if(!file_exists($parent)){ # check whether the parent directory exists
			if($recursive === false){ # if it should not be created automatically, throw an error
				throw new Exception("File » mkdir: parent directory does not exist: «{$parent}».");
			}

			static::mkdir($parent, recursive:true); # recursively create the parent directory
		}

		if(!mkdir($path)){ # create the directory
			throw new Exception("File » mkdir: failed to create directory «{$path}».");
		}

		# the new directory will have the same owner and group as the user that executes this script (usually
		# the webserver user, i.e. http or www-data)
		# set permissions: all to the owner and group, none to others (chmod 770)
		chmod($path, 0o770);
	}
}
?>
