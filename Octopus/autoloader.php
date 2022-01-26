<?php
spl_autoload_register(function($name){
	if($name == 'Astronauth\Config\Config'){
		require_once __DIR__ . '/Config/Astronauth.php';
		return;
	}

	$file = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
});
?>
