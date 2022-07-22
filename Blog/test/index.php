<?php
require_once __DIR__ . '/../Octopus/autoloader.php';
require_once __DIR__ . '/../vendor/kaiwessela/astronauth/autoloader.php';
require_once __DIR__ . '/../vendor/kaiwessela/parsedownforblog/autoloader.php';
require_once __DIR__ . '/TestController.php';

$endpoint = new \Octopus\Core\Controller\Endpoint([
	'modules' => [
		'controllers' => [
			'TestController' => '\Test\TestController',
		]
	]
]);

$endpoint->get_response()->set_templates([
	0 => 'start'
]);

$endpoint->execute();
?>
