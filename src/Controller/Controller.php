<?php
namespace Blog\Controller;
use \Blog\Controller\Processors\Pagination\Pagination;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\DatabaseException;
use InvalidArgumentException;
use Exception;

abstract class Controller {
	public $request;
	public $status;
	public $objects;
	public $errors;

	public $pagination;
	protected $count;

	const MODEL = '';


	function __construct($request) {
		$this->request = $request;
		$this->status = 10;
		$this->errors = [];
	}

	function __set($name, $value) {
		if($name == 'object'){
			$this->objects[0] = $value;
		}
	}

	function __get($name) {
		if($name == 'object'){
			return $this->objects[0];
		}
	}

	public function execute() {
		$model = '\Blog\Model\DatabaseObjects\\' . $this::MODEL;

		if($this->request->action == 'new'){
			$this->object = new $model();

			if($this->request->method == 'post'){
				try {
					$this->object->generate();
					$this->object->import($this->request->data);
					$this->object->push();
					$this->status = 21;
				} catch(InputFailedException $e){
					$this->status = 41;
					$this->errors[] = $e;
					return;
				} catch(Exception $e){
					$this->status = 50;
					throw $e;
					return;
				}
			} else {
				$this->status = 24;
			}

		} else if($this->request->action == 'show' || $this->request->action == 'edit' || $this->request->action == 'delete'){
			try {
				$this->object = new $model();
				$this->object->pull($this->request->identifier);
				$this->status = 20;
			} catch(EmptyResultException $e){
				$this->status = 44;
				return;
			} catch(Exception $e){
				$this->status = 50;
				throw $e;
				return;
			}

			if($this->request->action == 'edit' && $this->request->method == 'post'){
				try {
					$this->object->import($this->request->data);
					$this->object->push();
					$this->status = 22;
				} catch(InputFailedException $e){
					$this->status = 41;
					$this->errors[] = $e;
					return;
				} catch(Exception $e){
					$this->status = 50;
					throw $e;
					return;
				}
			} else if($this->request->action == 'delete' && $this->request->method == 'post'){
				try {
					$this->object->delete();
					$this->status = 23;
				} catch(Exception $e){
					$this->status = 50;
					throw $e;
					return;
				}
			}

		} else if($this->request->action == 'list'){
			$limit = $this->request->amount;

			if($this->request->page == null){
				$offset = null;
			} else {
				try {
					$this->count = $model::count();
				} catch(DatabaseException $e){
					$this->status = 50;
					throw $e;
					return;
				}

				if($this->count == 0){
					$this->objects = [];
					$this->status = 24;
					return;
				} else {
					$offset = $this->request->amount * ($this->request->page - 1);
					$last_page = ceil($this->count / $this->request->amount);

					if($this->request->page > $last_page || $this->request->page == 0){
						$this->status = 44;
						return;
					}
				}
			}

			try {
				$this->objects = $model::pull_all($limit, $offset);
				$this->status = 20;
			} catch(EmptyResultException $e){
				$this->status = 24;
				return;
			} catch(Exception $e){
				$this->status = 50;
				throw $e;
				return;
			}
		}
	}

	public function process() {
		$objs = [];
		foreach($this->objects as $object){
			$obj = $object->export();
			$this->process_each($object, $obj);
			$objs[] = $obj;
		}
		$this->objects = $objs;

		if($this->request->action == 'list' && isset($this->request->custom['pagination_structure'])){
			$current_page = $this->request->page;
			$objects_per_page = $this->request->amount;
			$total_objects = $this->count;
			$base_path = $this->request->router->resolve_substitutions($this->request->custom['pagination_base']);
			$structure = $this->request->custom['pagination_structure'];

			try {
				$this->pagination = new Pagination($current_page, $objects_per_page, $total_objects, $base_path, $structure);
			} catch(InvalidArgumentException $e){
				$this->exceptions[] = $e;
			}
		}

		$this->process_all($this->objects);
	}

	protected function process_all(&$objects) {
		return;
	}

	protected function process_each(&$object, &$obj) {
		return;
	}



	public function status(int $status) {
		return $this->status == $status;
	}

	public function processing() {			# INFO status
		return $this->status == 10;
	}

	public function found() {				# SUCCESS status
		return $this->status == 20;
	}

	public function created() {
		return $this->status == 21;
	}

	public function edited() {
		return $this->status == 22;
	}

	public function deleted() {
		return $this->status == 23;
	}

	public function empty() {
		return $this->status == 24;
	}

	public function bad_request() {			# CLIENT ERROR status
		return $this->status == 40;
	}

	public function unprocessable() {
		return $this->status == 41;
	}

	public function forbidden() {
		return $this->status == 43;
	}

	public function not_found() {
		return $this->status == 44;
	}

	public function internal_error() {		# SERVER ERROR status
		return $this->status == 50;
	}

/* ================================

STATUS:

10 Processing

20 Found
21 Created
22 Edited
23 Deleted
24 Empty

40 Bad Request
41 Unprocessable
43 Forbidden
44 Not Found

50 Internal Error

   ================================ */
}
?>
