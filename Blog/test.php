<?php
$config = [
	'db_name' => 'abcdefg',
	'db_pass' => 1234567,
	'test' => true
];

class Config {
	static array $config;

	public static function read(array $config) : void {
		static::$config = $config;
	}

	public static function get($key) : mixed {
		return static::$config[$key];
	}
}

Config::read($config);

echo Config::get('db_name').PHP_EOL;
echo Config::get('db_pass').PHP_EOL;
echo Config::get('test').PHP_EOL;
?>
