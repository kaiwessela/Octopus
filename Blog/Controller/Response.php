<?php
namespace Blog\Controller;
use Exception;

class Response { 
	public int $code;
	public string $content_type;
	public array $headers;


	function __construct() {
		$this->code = 200;
		$this->content_type = 'text/html';
		$this->headers = [];
	}


	public function set_code(int $code) : void {
		if(isset($this::RESPONSE_CODES[$code])){
			$this->code = $code;
		} else {
			throw new Exception('Response » invalid code.');
		}
	}

	public function set_content_type(string $content_type) : void {
		if(in_array($content_type, self::SUPPORTED_CONTENT_TYPES)){
			$this->content_type = $content_type;
		} else {
			throw new Exception('Response » content type not supported.');
		}
	}

	public function add_header(string $header) : void {
		$this->headers[] = $header;
	}


	public function send() {
		http_response_code($this->code);
		header('Content-Type: ' . $this->content_type);

		foreach($this->headers as $header){
			header($header);
		}
	}


	const SUPPORTED_CONTENT_TYPES = [
		'text/html',
		'application/json'
	];

	const RESPONSE_CODES = [
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found (Moved Temporarily)',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfied',
		417 => 'Expectation Failed',
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Too Early',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required'
	];
}
?>
