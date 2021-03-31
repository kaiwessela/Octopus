<?php
session_start();

require_once __DIR__ . '/../../Blog/autoloader.php';
require_once __DIR__ . '/../../vendor/kaiwessela/astronauth/autoloader.php';
require_once __DIR__ . '/../../vendor/kaiwessela/parsedownforblog/autoloader.php';

$endpoint = new \Blog\Controller\Endpoint(__DIR__.'/templates');
$endpoint->request->add_allowed_content_type('application/json');
$endpoint->response->set_content_type('application/json');
$endpoint->route(require __DIR__.'/routes.php');
$endpoint->prepare();
$endpoint->execute();

if($endpoint->response->code != 200){
	$endpoint->template = 'error';
}

$endpoint->send();

?>
