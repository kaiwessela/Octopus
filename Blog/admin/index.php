<?php
require_once __DIR__ . '/../Octopus/autoloader.php';
require_once __DIR__ . '/../vendor/kaiwessela/astronauth/autoloader.php';
require_once __DIR__ . '/../vendor/kaiwessela/parsedownforblog/autoloader.php';

$endpoint = new \Octopus\Core\Controller\Endpoint([
	'modules' => '{ENDPOINT_DIR}/config/modules.php'
]);

$endpoint->get_response()->set_templates([
	404 => '404',
	4 => 'error',
	5 => 'error'
]);

$endpoint->execute();
?>
