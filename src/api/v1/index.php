<?php
/* ################################

VALID ROUTES:
/
/posts (?limit=[int](&offset=[int]))
/posts/new
/posts/count
/posts/[id|longid]
/posts/[id|longid]/edit
/posts/[id|longid]/delete
/images (?limit=[int](&offset=[int]))
/images/new
/images/count
/images/[id|longid]
/images/[id|longid]/edit
/images/[id|longid]/delete

*/ ################################

session_start();

spl_autoload_register(function($name){
	$file = DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	$include = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . strtolower(str_replace(DIRECTORY_SEPARATOR . 'Blog', '', $file));

	if(file_exists($include)){
		require_once $include;
	}
});

spl_autoload_register(function($name){
	$file = DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
	$include = __DIR__ . DIRECTORY_SEPARATOR . '../../astronauth' . strtolower(str_replace(DIRECTORY_SEPARATOR . 'Astronauth', '', $file));

	if(file_exists($include)){
		require_once $include;
	}
});

$endpoint = new \Blog\Frontend\API\v1\Endpoint();
$endpoint->handle();
?>
