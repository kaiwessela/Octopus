<?php
session_start();

require_once __DIR__ . '/../../Octopus/autoloader.php';
require_once __DIR__ . '/../Blog/autoloader.php';

$endpoint = new \Octopus\Core\Controller\Endpoint([
	'config' => '{ENDPOINT_DIR}/config.php',
	'modules' => '{ENDPOINT_DIR}/modules.php',
	'routes' => '{ENDPOINT_DIR}/routes.php',
	'templates' => '{ENDPOINT_DIR}/templates'
]);

$endpoint->get_response()->set_templates([
	404 => 'error',
	4 => 'error',
	5 => 'error'
]);

$endpoint->execute();
?>
