<?php

class Config {
	private static array $config;


	public static function load(string $config_filename) : void {
		if(isset(static::$config)){
			throw new Exception('Config has already been loaded.');
		}


	}


	protected static function include(string $base_key, string $current_dir, string $filename) : void {
		$path = $current_dir . $filename;

		if(!file_exists($path)){
			throw new Exception("Config file not found: «{$path}».");
		}

		$raw = include $path;

		if(!is_array($raw)){
			throw new Exception("Config file invalid: «{$path}».");
		}

		foreach($raw['@include'] as $key => $file){
			
		}

	}


	public static function get(string $name, ?string $expected_type = null, bool $quiet = false) : mixed {

	}
}
?>
