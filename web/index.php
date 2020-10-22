<?php
session_start();

spl_autoload_register(function($name){
	$file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
});

spl_autoload_register(function($name){
	$file = __DIR__ . '/' . strtolower(str_replace('\\', '/', $name)) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
});

define('TEMPLATE_PATH', __DIR__ . '/Blog/View/Templates/');
define('COMPONENT_PATH', __DIR__ . '/Blog/View/Components/');

require_once 'libs/parsedown/Parsedown.php'; // TODO include into autoloader

$endpoint = new \Blog\EndpointHandler();
?>
