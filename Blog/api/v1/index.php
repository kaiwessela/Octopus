<?php
require_once __DIR__ . '/../../Octopus/autoloader.php';
require_once __DIR__ . '/../../vendor/kaiwessela/astronauth/autoloader.php';
require_once __DIR__ . '/../../vendor/kaiwessela/parsedownforblog/autoloader.php';

$endpoint = new \Octopus\Core\Controller\Endpoint([
	'modules' => '{ENDPOINT_DIR}/modules.php'
]);

$endpoint->get_response()->set_content_type('application/json');
$endpoint->get_response()->set_templates([
	4 => '4xx',
	5 => 'error'
]);

$endpoint->execute();
?>
