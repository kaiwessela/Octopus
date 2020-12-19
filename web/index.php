<?php
session_start();

spl_autoload_register(function($name){
	$file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
});

require_once 'libs/Astronauth/autoloader.php';
require_once 'libs/Parsedown/autoloader.php';

define('TEMPLATE_PATH', __DIR__ . '/Blog/View/Templates/');
define('COMPONENT_PATH', __DIR__ . '/Blog/View/Components/');

$endpoint = new \Blog\EndpointHandler();
?>
