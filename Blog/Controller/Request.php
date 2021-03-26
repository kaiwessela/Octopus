<?php
namespace Blog\Controller;
use Exception;

class Request {
						# https://example.org/test/index.php?query=true
	public string $url;	# https://example.org/test/index.php
	public string $host;	#     example.org
	public ?string $path;	#                /test/index.php
	public ?string $query;	#                                query=true

	public string $method;
	public ?string $content_type;

	public ?array $post;
	public ?array $get;

	private ?array $allowed_methods;
	private ?array $allowed_content_types;


	function __construct() {
		$this->url = $_SERVER['REQUEST_URI'];
		$this->host = $_SERVER['HTTP_HOST'];
		$this->path = $_SERVER['SCRIPT_URL'] ?? null;
		$this->query = $_SERVER['QUERY_STRING'] ?? null;

		$this->get = $_GET;

		$this->method = $_SERVER['REQUEST_METHOD'];
		if($this->method != 'GET' && $this->method != 'POST'){
			// 405 Method Not Allowed
		}

		if($this->method == 'GET'){
			$this->content_type = null;
			$this->post = null;
		} else if($_SERVER['CONTENT_TYPE'] == 'application/json'){
			$this->content_type = 'application/json';
			$this->post = json_decode(file_get_contents('php://input'), true); // TODO Exception on error
		} else if($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded'){
			$this->content_type = 'application/x-www-form-urlencoded';
			$this->post = $_POST;
		} else if(preg_match('/^multipart\/form-data.*/', $_SERVER['CONTENT_TYPE'])){
			$this->content_type = 'multipart/form-data';
			$this->post = $_POST;
		} else {
			// 415 Unsupported Media Type
		}

		$this->allowed_methods = null;
		$this->allowed_content_types = null;
	}


	public function is_get() : bool {
		return $this->method == 'GET';
	}

	public function is_post() : bool {
		return $this->method == 'POST';
	}

	public function GET($key) {

	}

	public function POST($key) {

	}


	public function set_allowed_methods(?array $methods) : void {
		if(is_null($methods)){
			$this->allowed_methods = null;
		} else {
			$this->allowed_methods = [];
			foreach($methods as $method){
				$this->add_allowed_method($method);
			}
		}
	}

	public function add_allowed_method(string $method) : void {
		if(is_null($this->allowed_methods)){
			$this->allowed_methods = [];
		}

		if(in_array($method, $this->allowed_methods)){
			return;
		} else if(in_array($method, self::SUPPORTED_METHODS)){
			$this->allowed_methods[] = $method;
		} else {
			throw new Exception('Request » method not supported.');
		}
	}

	public function merge_allowed_methods(array $methods) : void {
		if(is_null($this->allowed_methods)){
			$this->set_allowed_methods($methods);
		} else {
			$this->set_allowed_methods(array_intersect($this->allowed_methods, $methods));
		}
	}

	public function check_method() : bool {
		return is_null($this->allowed_methods) ?: in_array($this->method, $this->allowed_methods);
	}


	public function set_allowed_content_types(?array $content_types) : void {
		if(is_null($content_types)){
			$this->allowed_methods = null;
		} else {
			$this->allowed_content_types = [];
			foreach($content_types as $content_type){
				$this->add_allowed_content_type($content_type);
			}
		}
	}

	public function add_allowed_content_type(string $content_type) : void {
		if(is_null($this->allowed_content_types)){
			$this->allowed_content_types = [];
		}

		if(in_array($content_type, $this->allowed_content_types)){
			return;
		} else if(in_array($content_type, self::SUPPORTED_CONTENT_TYPES)){
			$this->allowed_content_types[] = $content_type;
		} else {
			throw new Exception('Request » content type not supported.');
		}
	}

	public function merge_allowed_content_types(array $content_types) : void {
		if(is_null($this->allowed_content_types)){
			$this->set_allowed_content_types($content_types);
		} else {
			$this->set_allowed_content_types(array_intersect($this->allowed_content_types, $content_types));
		}
	}

	public function check_content_type() : bool {
		if($this->method != 'POST' || is_null($this->allowed_content_types)){
			return true;
		} else {
			return in_array($this->content_type, $this->allowed_content_types);
		}
	}



	const SUPPORTED_METHODS = ['GET', 'POST'];

	const SUPPORTED_CONTENT_TYPES = [
		'application/json',
		'application/x-www-form-urlencoded',
		'multipart/form-data'
	];


}
?>
