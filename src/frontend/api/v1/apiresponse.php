<?php
namespace Blog\Frontend\API\v1;

class APIResponse {
	# APIResponse class – container and handler for response information

	public $response = [			# response to be sent as json
		'api_status'	=> null,	# api status message
		'response_code'	=> null,	# HTTP response code
		'error_message'	=> null,	# error message (if an error occurs)
		'result'		=> null		# requested resources
	];

	public $response_code = 200;	# HTTP response code
	public $headers = [];			# HTTP headers


	public function set_api_status($status) {
		$this->response['api_status'] = $status;
	}

	public function set_error_message($msg) {
		$this->response['error_message'] = $msg;
	}

	public function set_result($result) {
		$this->response['result'] = $result;
	}

	public function set_header($header) {
		$this->headers[] = $header;
	}

	public function set_response_code($code = 200) {
		switch ($code) {
			case 200: $this->response['response_code'] = '200 OK';						break;
			case 400: $this->response['response_code'] = '400 Bad Request';				break;
			case 404: $this->response['response_code'] = '404 Not Found'; 				break;
			case 405: $this->response['response_code'] = '405 Method Not Allowed';		break;
			case 500: $this->response['response_code'] = '500 Internal Server Error';	break;
		}

		$this->response_code = $code;
	}

	public function send() {
		foreach($this->headers as $header){
			header($header);
		}

		http_response_code($this->response_code);

		echo json_encode($this->response);

		exit;
	}
}
?>
