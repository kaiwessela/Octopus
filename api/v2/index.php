<?php
session_start();

// TODO autoloader

$endpoint = new \Blog\Controller\Endpoint();
$endpoint->request->add_allowed_content_type('application/json');
$endpoint->response->set_content_type('application/json');
$endpoint->route();
$endpoint->prepare();

?>
