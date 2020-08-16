<?php
namespace Blog\Frontend\API\v1;
use \Blog\Config\Config;
use \Blog\Frontend\API\v1\APIRequest;
use \Blog\Frontend\API\v1\APIResponse;
use \Blog\Backend\Models\Post;
use \Blog\Backend\Models\Image;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\InvalidInputException;
use PDO;
use PDOException;
use InvalidArgumentException;

class Endpoint {
	private $pdo;
	private $request;
	private $response;

	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$this->request = new APIRequest();
		$this->response = new APIResponse();

		# set default response values
		$this->response->set_api_status('kaiwessela/blog API v1 – running.');
		$this->response->set_header('Content-Type: application/json');

		# establish database connection
		try {
			$this->pdo = new PDO(
				'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
				Config::DB_USER,
				Config::DB_PASSWORD
			);
		} catch(PDOException $e){
			# database connection failed, answer with error
			$this->response->set_api_status('kaiwessela/blog API v1 – degraded.');
			$this->response->set_response_code(500);
			$this->response->set_error_message('PDO: Connection failed – ' . $e->getMessage());
			$this->response->send();
		}
	}

	public function handle() {

		# CLASS HANDLING MODULE
		if(!isset($this->request->class)){
			# no class requested, answer only with api status
			$this->response->set_response_code(200);
			$this->response->send();
		} else if($this->request->class == 'posts'){
			# class Post requested
			$backend_class = new \Blog\Backend\Models\Post;
		} else if($this->request->class == 'images'){
			# class Image requested
			$backend_class = new \Blog\Backend\Models\Image;
		} else if($this->request->class == 'persons'){
			# class Person requested
			$backend_class = new \Blog\Backend\Models\Person;
		} else {
			# invalid class requested, answer with error
			$this->response->set_response_code(400);
			$this->response->set_error_message('API: invalid class.');
			$this->response->send();
		}


		# IDENTIFIER HANDLING MODULE
		if(!isset($this->request->identifier)){
			# no identifier specified -> return all instances of class

			$limit = null;
			$offset = null;
			if(isset($this->request->query_string['limit'])){
				$limit = (int) $this->request->query_string['limit'];

				if(isset($this->request->query_string['offset'])){
					$offset = (int) $this->request->query_string['offset'];
				}
			}

			try {
				# try to pull all instances of class
				$objs = $backend_class->pull_all($limit, $offset);
			} catch(EmptyResultException $e){
				# no instances found, answer with error
				$this->response->set_response_code(404);
				$this->response->set_error_message('API: no objects found.');
				$this->response->send();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message('API: internal database error.');
				$this->response->send();
			} catch(InvalidArgumentException $e){
				# invalid argument supplied, answer with error
				$this->response->set_response_code(400);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# everything worked, return objects
			$this->response->set_response_code(200);
			$this->response->set_result($objs);
			$this->response->send();

		} else if($this->request->identifier == 'new') {
			# generic identifier 'new' specified -> insert a new instance of class
			# check if Request-Method is POST
			if($this->request->method != 'POST'){
				# Request-Method must be POST but isn't, answer with error
				$this->response->set_response_code(405);
				$this->response->set_error_message('API: invalid request method.');
				$this->response->send();
			}

			# Request-Method is valid
			# create new instance of class
			$backend_class->generate();

			try {
				# try to insert post data into the instance
				$backend_class->import($this->request->post);
				$backend_class->push();
			} catch(InvalidInputException $e){
				# post data is invalid, answer with error
				$this->response->set_response_code(400);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# everything worked, return object
			$this->response->set_response_code(200);
			$this->response->set_result($backend_class);
			$this->response->send();

		} else if($this->request->identifier == 'count'){
			# generic identifier 'count' specified -> return the amount of instances of class available
			try {
				# try to count all instances of class
				$count = $backend_class->count();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# everything worked, return count
			$this->response->set_response_code(200);
			$this->response->set_result($count);
			$this->response->send();

		} else {
			# object-specific identifier specified -> pull requested instance of class, handle depending on specified action
			try {
				# try to pull the specified instance of class
				$backend_class->pull($this->request->identifier);
			} catch(EmptyResultException $e){
				# instance not found, answer with error
				$this->response->set_response_code(404);
				$this->response->set_error_message('API: object not found.');
				$this->response->send();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# proceed in the action handling module

		}


		# ACTION HANDLING MODULE
		if(!isset($this->request->action)){
			# no action specified -> return instance of class
			$this->response->set_response_code(200);
			$this->response->set_result($backend_class);
			$this->response->send();

		} else if($this->request->action == 'edit'){
			# action 'edit' specified -> edit instance of class
			# check if Request-Method is POST
			if($this->request->method != 'POST'){
				# Request-Method must be POST but isn't, answer with error
				$this->response->set_response_code(405);
				$this->response->set_error_message('API: invalid request method.');
				$this->response->send();
			}

			try {
				# try to update the object
				$backend_class->import($this->request->post);
				$backend_class->push();
			} catch(InvalidInputException $e){
				# post data is invalid, answer with error
				$this->response->set_response_code(400);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# everything worked, return object
			$this->response->set_response_code(200);
			$this->response->set_result($backend_class);
			$this->response->send();

		} else if($this->request->action == 'delete'){
			# action 'delete' specified -> delete instance of class
			try {
				# try to delete the object
				$backend_class->delete();
			} catch(DatabaseException $e){
				# internal database exception, answer with error
				$this->response->set_response_code(500);
				$this->response->set_error_message($e->getMessage());
				$this->response->send();
			}

			# everything worked, return object
			$this->response->set_response_code(200);
			$this->response->set_result($backend_class);
			$this->response->send();

		} else {
			# invalid action specified, answer with error
			$this->response->set_response_code(400);
			$this->response->set_error_message('API: invalid action.');
			$this->response->send();

		}
	}
}
?>
