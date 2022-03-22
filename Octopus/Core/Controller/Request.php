<?php
namespace Octopus\Core\Controller;

class Request {
	private bool $https;
	private string $host;	# example.org
	private string $port;	# 80
	private ?string $path; # /test/index.php
	private ?string $query; # query=true&test=1

	private ?string $virtual_path;

	private string $method; # GET|POST|PUT|DELETE|…
	private ?string $content_type;


	function __construct() {
		$this->host = $_SERVER['SERVER_NAME'];
		$this->port = $_SERVER['SERVER_PORT'];
		$this->path = rtrim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], '/').'/';
		$this->query = $_SERVER['QUERY_STRING'];
		$this->https = !empty($_SERVER['HTTPS']);

		$this->method = $_SERVER['REQUEST_METHOD'];
		$content_type = explode(';', $_SERVER['CONTENT_TYPE'] ?? '', 2)[0];
		$this->content_type = ($content_type !== '') ? $content_type : null;

		$this->virtual_path = ltrim(substr($this->path, strlen(dirname($_SERVER['SCRIPT_NAME']))), '/');

		// var_dump($_SERVER);
		//
		// echo "Host: $this->host".PHP_EOL;
		// echo "Port: $this->port".PHP_EOL;
		// echo "Path: $this->path".PHP_EOL;
		// echo "Query: $this->query".PHP_EOL;
		// echo "Method: $this->method".PHP_EOL;
		// echo "Content Type: $this->content_type".PHP_EOL;
		// echo "Virtual Path: $this->virtual_path".PHP_EOL;
	}


	public function get_host() : string {
		return $this->host;
	}

	public function get_port() : int {
		return $this->port;
	}

	public function get_path() : ?string {
		return $this->path;
	}

	public function get_path_segment(int $segment) : ?string {
		if($segment < 1){
			return null;
		}

		$result = explode('/', $this->get_path(), $segment+2)[$segment];
		return empty($result) ? null : $result;
	}

	public function get_path_from_segment(int $segment) : ?string {
		if($segment < 1){
			return null;
		}

		$result = explode('/', $this->get_path(), $segment+2)[$segment+1];
		return empty($result) ? null : '/'.$result;
	}

	public function get_virtual_path() : ?string {
		return $this->virtual_path;
	}

	public function get_virtual_path_segment(int $segment) : ?string {
		if($segment < 1){
			return null;
		}

		$result = explode('/', $this->get_virtual_path(), $segment+2)[$segment]; // TODO check all these
		return empty($result) ? null : $result;
	}

	public function get_virtual_path_from_segment(int $segment) : ?string {
		if($segment < 1){
			return null;
		}

		$result = explode('/', $this->get_virtual_path(), $segment+2)[$segment-1];
		return empty($result) ? null : $result;
	}

	public function get_query_string() : ?string {
		return $this->query;
	}

	public function get_query_value(string $key) : ?string {
		return $_GET[$key] ?? null;
	}

	public function is_https() : bool {
		return $this->https;
	}

	public function get_method() : string {
		return $this->method;
	}

	public function get_content_type() : ?string {
		return $this->content_type;
	}

	public function get_base_url() : string {
		$protocol = $this->is_https() ? 'https' : 'http';
		$port = ($this->get_port() === 80 || $this->get_port() === 443) ? '' : ":{$this->get_port()}";

		return "{$protocol}://{$this->get_host()}{$port}";
	}
}
?>
