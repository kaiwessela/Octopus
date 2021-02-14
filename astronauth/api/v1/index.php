<?php
/*
/api/v1/
/api/v1/authenticate
/api/v1/signin
/api/v1/signout
/api/v1/signup
/api/v1/account
/api/v1/account/edit
/api/v1/account/delete
/api/v1/account/devices
/api/v1/account/devices/[id]
/api/v1/account/devices/[id]/disable
/api/v1/account/devices/[id]/delete
*/

require __DIR__ . '/../vendor/kaiwessela/astronauth/autoloader.php';

$astronauth = new \Astronauth\Main();
?>
