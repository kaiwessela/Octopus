<?php
namespace Octopus\Core;
use \Octopus\Core\Controller\ConfigLoader;
use \Octopus\Core\Controller\Exceptions\ControllerException;

// TEMP this is unfinished and not safe yet

class Config {
	private static array $config;


	public static function load(string|array $path_or_config) : void {
		if(isset(self::$config)){
			throw new ControllerException(500, 'Config has already been loaded.');
		}

		if(is_string($path_or_config)){
			self::$config = ConfigLoader::read($path_or_config);
		} else {
			self::$config = $path_or_config;
		}
	}


	protected static function has(string $name) : bool {
		return self::_get($name, self::$config, true, true);
	}


	public static function get(string $name, bool $quiet = false) : mixed {
		return self::_get($name, self::$config, $quiet, false);
	}


	private static function _get(string $name, array $cfg, bool $quiet, bool $isset_only) : mixed {
		@[$n, $nr] = explode('.', $name, 2);

		if(isset($cfg[$n])){
			if(empty($nr)){
				if($isset_only){
					return true;
				} else {
					return $cfg[$n];
				}
			} else {
				return self::_get($nr, $cfg[$n], $quiet, $isset_only);
			}
		} else if($quiet){
			if($isset_only){
				return false;
			} else {
				return null;
			}
		} else {
			throw new ControllerException(500, "Config value not found: «{$name}»."); // TODO this only shows the last part(s)
		}
	}
}
?>
