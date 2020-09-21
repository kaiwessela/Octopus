<?php
session_start();

spl_autoload_register(function($name){
	$file = DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	$include = __DIR__ . DIRECTORY_SEPARATOR . '..' . strtolower(str_replace(DIRECTORY_SEPARATOR . 'Blog', '', $file));

	if(file_exists($include)){
		require_once $include;
	}
});

spl_autoload_register(function($name){
	$file = DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	$include = __DIR__ . DIRECTORY_SEPARATOR . '../astronauth' . strtolower(str_replace(DIRECTORY_SEPARATOR . 'Astronauth', '', $file));

	if(file_exists($include)){
		require_once $include;
	}
});

define('TEMPLATE_PATH', __DIR__ . '/frontend/admin/templates/');
define('COMPONENT_PATH', __DIR__ . '/frontend/admin/components/');

$endpoint = new \Blog\Frontend\Admin\Endpoint();
$endpoint->handle();
?>
