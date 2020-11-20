<?php
namespace Blog\Controller;
use \Blog\Controller\Processors\Pagination\Pagination;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exportable;
use InvalidArgumentException;
use Exception;

abstract class Controller {
	public $request;
	public $status;
	public $object;
	public $errors;

	public $pagination;

	const MODEL = '';
	const LIST_MODEL = '';


	function __construct($request) {
		$this->request = $request;
		$this->status = 50;
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
		if($this->request->mode == 'multi'){
			$model = '\Blog\Model\DataObjects\Lists\\' . $this::LIST_MODEL;
			$this->object = new $model();

			$limit = $this->request->amount;

			if($this->request->page == null){
				$offset = null;
			} else if($this->object->count() == 0){
				$this->status = 24;
				return;
			} else {
				$offset = $this->request->amount * ($this->request->page - 1);
				$last_page = ceil($this->object->count / $this->request->amount);

				if($this->request->page > $last_pate || $this->request->page == 0){
					$this->status = 44;
					return;
				}
			}

			try {
				$this->object->pull($limit, $offset);
				$this->status = 20;
			} catch(EmptyResultException $e){
				$this->status = 24;
				return;
			}

		} else {
			$model = '\Blog\Model\DataObjects\\' . $this::MODEL;
			$this->object = new $model();

			if($this->request->action == 'new'){
				if($this->request->method == 'post'){
					$this->object->generate();

					try {
						$this->object->import($this->request->data);
						$this->object->push();
						$this->status = 21;
					} catch(InputFailedException $e){
						$this->status = 41;
						$this->errors[] = $e;
						return;
					}
				} else {
					$this->status = 24;
				}

			} else {
				try {
					$this->object->pull($this->request->identifier);
				} catch(EmptyResultException $e){
					$this->status = 44;
					return;
				}

				if($this->request->action == 'show' || $this->request->method == 'get'){
					$this->status = 20;
					return;

				} else if($this->request->action == 'edit'){
					try {
						$this->object->import($this->request->data);
						$this->object->push();
						$this->status = 22;
					} catch(InputFailedException $e){
						$this->status = 41;
						$this->errors[] = $e;
						return;
					}

				} else if($this->request->action == 'delete'){
					$this->object->delete();
					$this->status = 23;
					return;
				}
			}
		}
	}

	public function export() {
		// TEMP
		return $this->object->export();


		// --------
		
		# export errors
		$errs = [];
		foreach($this->errors as $error){
			if($error instanceof Exportable){
				$err = $error->export();
				$errs[$error->export_name] = $err;
			}
		}
		$this->errors = $errs;


		if($this->request->mode == 'multi'){
			$this->export_multi();
		} else {
			$this->export_single();
		}
	}

	protected function export_multi() {

	}

	protected function export_single() {
		$obj = $this->object->export();

	}

	public function process() {
		$objs = [];
		foreach($this->objects as $object){
			$obj = $object->export();
			$this->process_each($object, $obj);
			$objs[] = $obj;
		}
		$this->objects = $objs;

		$errs = [];
		foreach($this->errors as $error){
			if($error instanceof Exportable){
				$err = $error->export();
				$errs[$error->export_name] = $err;
			}
		}
		$this->errors = $errs;

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
