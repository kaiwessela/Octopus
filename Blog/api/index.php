<?php
http_response_code(400);
header('Content-Type: application/json');
echo <<<JSON
{
	"code": 400,
	"message": "Welcome. This Endpoint is invalid. API available under /api/v1"
}
JSON;
?>
