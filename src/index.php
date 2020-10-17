<?php
session_start();

spl_autoload_register(function($name){
	$file = DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	$include = __DIR__ . strtolower(str_replace(DIRECTORY_SEPARATOR . 'Blog', '', $file));

	if(file_exists($include)){
		require_once $include;
	}
});

define('TEMPLATE_PATH', __DIR__ . '/frontend/web/templates/');
define('COMPONENT_PATH', __DIR__ . '/frontend/web/templates/components/');

require_once 'libs/parsedown/Parsedown.php';

$endpoint = new \Blog\Frontend\Web\Endpoint();
$endpoint->handle();
?>
