<?php
spl_autoload_register(function($name){
	$file = __DIR__ . '/../' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';

	if(file_exists($file)){
		require_once $file;
	}
})
?>
