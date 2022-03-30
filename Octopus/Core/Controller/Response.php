<?php
namespace Octopus\Core\Controller;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Core\Controller\Exceptions\ControllerException;

class Response {
	private ?string $template_dir;
	private string $content_type;
	private array $templates;


	function __construct() {
		$this->template_dir = null;
		$this->content_type = 'text/html';
		$this->templates = [];
	}


	public function set_status_code(int $code) : void {
		if(!in_array($code, self::RESPONSE_CODES)){
			throw new ControllerException(500, "Status code invalid: «{$code}».");
		}

		$this->status_code = $code;
	}


	public function set_content_type(string $content_type) : void {
		$this->content_type = $content_type;
	}


	public function set_template_dir(string $path) : void {
		$dir = realpath(ConfigLoader::resolve_path($path));

		if($dir === false){
			throw new ControllerException(500, "Template directory not found: «{$path}».");
		}

		if(!is_dir($dir)){
			throw new ControllerException(500, "Template directory is not a directory: «{$dir}».");
		}

		if(!str_starts_with($dir, ConfigLoader::get_document_root())){
			throw new ControllerException(500, "Template directory is outside the document root: «{$dir}».");
		}

		if(!is_readable($dir)){
			throw new ControllerException(500, "Template directory is not readable: «{$dir}».");
		}

		$this->template_dir = $dir;
	}


	public function set_template(int $code, ?string $template) : void {
		if(is_null($this->template_dir)){
			throw new ControllerException(500, "Template directory not set, but must be set before setting templates.");
		} else if(is_null($template)){
			unset($this->templates[$code]);
			return;
		} else if(!in_array($code, self::STATUS_CODES) && !in_array($code, [0, 2, 3, 4, 5])){
			throw new ControllerException(500, "Template code invalid: «{$code}».");
		}

		$path = realpath($this->template_dir . DIRECTORY_SEPARATOR . trim($template, DIRECTORY_SEPARATOR) . '.php');

		if($path === false){
			throw new ControllerException(500, "Template not found: «{$template}».");
		} else if(!is_file($path) || !is_readable($path)){
			throw new ControllerException(500, "Template is not a file or not readable: «{$path}».");
		}

		$this->templates[$code] = $path;
	}


	public function set_templates(?array $templates = null) : void {
		if(is_null($templates)){
			$this->templates = [];
			return;
		}

		foreach($templates as $code => $template){
			$this->set_template($code, $template);
		}
	}


	public function send(int $code = 200, array $environment = []) : void {
		http_response_code($code);
		header("Content-Type: {$this->content_type}");

		$template = $this->templates[$code] ?? $this->templates[floor($code / 100)] ?? $this->templates[0] ?? null;

		if(is_null($template)){
			if($code === 500){
				die('Error 500 – Internal Server Error: No Template specified.');
			} else {
				throw new ControllerException(500, 'No Template specified.');
			}
		}

		$send = function($tmp, $env){
			foreach($env as $var => &$value){
				$$var = &$value;
			}

			unset($env);
			unset($var);
			unset($value);

			require $tmp;
		};

		$send($template, $environment);
	}


	public function include_template(string $template) : void { // IDEA
		if(is_null($this->template_dir)){
			throw new ControllerException(500, "Template directory not set, but must be set before including.");
		}

		$path = realpath($this->template_dir . DIRECTORY_SEPARATOR . trim($template, DIRECTORY_SEPARATOR) . '.php');

		if($path === false){
			throw new ControllerException(500, "Template not found: «{$path}».");
		} else if(!is_file($path) || !is_readable($path)){
			throw new ControllerException(500, "Template is not a file or not readable: «{$path}».");
		}

		$include = function($tmp, $env){
			foreach($env as $var => &$value){
				$$var = &$value;
			}

			unset($env);
			unset($var);
			unset($value);

			require $tmp;
		};

		$include($template, $environment);
	}


	const STATUS_CODES = [
		200, 201, 202, 203, 204, 205, 206,
		300, 301, 302, 303, 304, 305,      307, 308,
		400, 401,      403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
			 421, 422, 423, 424, 425, 426, 427, 428, 429,
		500, 501, 502, 503, 504,      506, 507
	];
}
?>
