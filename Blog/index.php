<?php
require_once __DIR__ . '/../Octopus/autoloader.php';
require_once __DIR__ . '/Blog/autoloader.php';
// require_once __DIR__ . '/../vendor/kaiwessela/astronauth/autoloader.php';
// require_once __DIR__ . '/../vendor/kaiwessela/parsedownforblog/autoloader.php';

$endpoint = new \Octopus\Core\Controller\Endpoint([
	'modules' => '{ENDPOINT_DIR}/modules.php',
	'routes' => '{ENDPOINT_DIR}/routes.php',
	'templates' => '{ENDPOINT_DIR}/templates'
]);

$endpoint->get_response()->set_templates([
	404 => '404',
	4 => 'error',
	5 => 'error'
]);

$endpoint->execute();

?>
