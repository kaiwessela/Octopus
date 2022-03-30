<?php
namespace Octopus\Core\Controller;
use \Octopus\Core\Controller\Exceptions\ControllerException;

class ConfigLoader {

	public static function read(string $path) : array {
		$absolute_path = static::resolve_path($path);
		$real_path = realpath($absolute_path);

		if($real_path === false){
			throw new ControllerException(500, "File not found: «{$absolute_path}».");
		}

		if(!is_file($real_path) || !is_readable($real_path)){
			throw new ControllerException(500, "File not readable or is not a file: «{$real_path}».");
		}

		if(!str_starts_with($real_path, static::get_document_root())){
			throw new ControllerException(500, "File is located outside of the document root: «{$real_path}».");
		}

		$include_sandbox = static function($file) {
			return include $file;
		};

		$config = $include_sandbox($real_path);

		if(!is_array($config)){
			throw new ControllerException(500, "File contents are invalid: «{$real_path}».");
		}

		return $config;
	}


	public static function resolve_path(string $path) : string { // TODO not optimal
		if(str_starts_with($path, '/')){
			return $path;
		} else if(str_starts_with($path, '{ENDPOINT_DIR}'.DIRECTORY_SEPARATOR)){
			return str_replace('{ENDPOINT_DIR}'.DIRECTORY_SEPARATOR, static::get_endpoint_dir(), $path);
		} else if(str_starts_with($path, '{OCTOPUS_DIR}'.DIRECTORY_SEPARATOR)){
			return str_replace('{OCTOPUS_DIR}'.DIRECTORY_SEPARATOR, static::get_octopus_dir(), $path);
		} else if(str_starts_with($path, '{DOCUMENT_ROOT}'.DIRECTORY_SEPARATOR)){
			return str_replace('{DOCUMENT_ROOT}'.DIRECTORY_SEPARATOR, static::get_document_root(), $path);
		} else {
			// TODO what to do with relative paths?
			throw new Exception('relative paths are not yet implemented.');
		}
	}


	public static function get_document_root() : string {
		return $_SERVER['DOCUMENT_ROOT'];
	}


	public static function get_octopus_dir() : string {
		return dirname(__DIR__, 2).DIRECTORY_SEPARATOR; # NOTE: This value is relative to this file’s location inside Octopus
	}


	public static function get_endpoint_dir() : string {
		return dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
	}
}
?>
