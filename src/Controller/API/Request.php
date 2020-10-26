<?php
namespace Blog\Controller\API;

class Request {
	# APIRequest class – container for request information

	public $method;			# HTTP Request-Method
	public $content_type;	# HTTP Content-Type (only if request is a POST request)

	public $class;			# class selector	– 1st part of path: /api/v1/xxx/.../...
	public $identifier;		# object identifier	– 2nd part of path: /api/v1/.../xxx/...
	public $action;			# requested action	– 3rd part of path: /api/v1/.../.../xxx

	public $query_string;	# request query string

	public $post;			# data received if request is a POST request


	function __construct() {
		$this->method = $_SERVER['REQUEST_METHOD'];

		$path = trim($_SERVER['REQUEST_URI'], '/');
		$segments = explode('/', $path);

		$this->class = $segments[2] ?? null;
		$this->identifier = $segments[3] ?? null;
		$this->action = $segments[4] ?? null;

		$this->query_string = $_GET;

		if($this->method == 'POST'){
			$this->content_type = $_SERVER['CONTENT_TYPE'] ?? 'multipart/form-data';

			# if data is received as json, the default php $_POST variable does not work
			if($this->content_type == 'application/json'){
				# receive data as json, decode it, and write it into post
				$this->post = json_decode(file_get_contents('php://input'), true);
			} else {
				# use default method
				$this->post = $_POST;
			}
		} else {
			$this->content_type = null;
			$this->post = null;
		}
	}
}
?>
