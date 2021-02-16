<?php
/* ################################

ROUTES:
/										GET		- welcome
/[class](?limit=[int](&offset=[int]))	GET		- list all (or some) instances of class
/[class]/new							POST	- create new instance of class
/[class]/count							GET		- count all instances of class
/[class]/[id|longid]					GET		- show requested instance of class
/[class]/[id|longid]/edit				POST	- edit requested instance of class
/[class]/[id|longid]/delete				POST	- delete requested instance of class

*/ ################################

session_start();

spl_autoload_register(function($name){
	$file = __DIR__ . '/../../' . str_replace('\\', '/', $name) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
});

spl_autoload_register(function($name){
	if($name == 'Astronauth\Config\Config'){
		require_once __DIR__ . DIRECTORY_SEPARATOR . '../../Blog/Config/Astronauth.php';
	}
});

require_once '../../vendor/kaiwessela/astronauth/autoloader.php';
require_once '../../vendor/kaiwessela/parsedownforblog/autoloader.php';

$endpoint = new \Blog\APIEndpointHandler();
$endpoint->handle();
?>
