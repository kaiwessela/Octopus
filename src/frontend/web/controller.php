<?php
namespace Blog\Frontend\Web;

abstract class Controller {
	private $request;
	public $status;
	public $objects;


	function __construct($request) {
		$this->request = $request;
		$this->status = 102;
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
		$model = '\Blog\Backend\Models\\' . $this::MODEL;

		if($this->request->action == 'new'){
			$this->object = new $model();

			if($this->request->method == 'post'){
				try {
					$this->object->generate();
					$this->object->import($this->request->data);
					$this->object->push();
					$this->status = 21;
				} catch(InvalidInputException $e){
					$this->status = 41;
					return;
				} catch(Exception $e){
					$this->status = 50;
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
				return;
			}

			if($this->request->action == 'edit' && $this->request->method == 'post'){
				try {
					$this->object->import($this->request->data);
					$this->object->push();
					$this->status = 22;
				} catch(InvalidInputException $e){
					$this->status = 41;
					return;
				} catch(Exception $e){
					$this->status = 50;
					return;
				}
			} else if($this->request->action == 'delete' && $this->request->method == 'post'){
				try {
					$this->object->delete();
					$this->status = 23;
				} catch(Exception $e){
					$this->status = 50;
					return;
				}
			}

		} else if($this->request->action == 'list'){
			$limit = $this->request->amount;

			if($this->request->page == null){
				$offset = null;
			} else {
				try {
					$count = $model::count();
				} catch(DatabaseException $e){
					$this->status = 50;
					return;
				}

				if($count == 0){
					$this->objects = [];
					$this->status = 24;
					return;
				} else {
					$offset = $this->request->amount * ($this->request->page - 1);
					$last_page = ceil($count / $this->request->amount);

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
				return;
			}
		}
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
