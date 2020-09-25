<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\ControllerModules;
use \Blog\Frontend\Web\Controllers\Exceptions\InvalidParameterException;
use \Blog\Backend\Exceptions\EmptyResultException;

/*# IDEA:
[
	action: 'list' | 'show' | 'new' | 'edit' | 'delete'

	#for action=list
	amount: int(>0)
	page: int(>0)

	#for action=show||edit||delete
	identifier: string, required
]

BUG: Pagination cannot use relative paths. There must be a way to provide an absolute path for the
pagination links.

*/# ---

abstract class Controller {
	const MODEL = null;

	public $action;
	public $errors;

	protected $params;

	public $objects;

	use ControllerModules;


	function __construct() {
		$this->params = (object) [];
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

	public function prepare($parameters) {
		$this->prepare_action($parameters);

		if($this->action == 'list'){
			$this->prepare_amount($parameters);
			$this->prepare_page($parameters);
		} else if($this->action == 'show' || $this->action == 'edit' || $this->action == 'delete'){
			$this->prepare_identifier($parameters);
		}
	}

	public function execute() {
		$model = '\Blog\Backend\Models\\' . $this::MODEL;

		if($this->action == 'new'){
			$this->objects[0] == new $model();
			$this->action->set_state('ready');

			if($_POST){
				try {
					$this->objects[0]->generate();
					$this->objects[0]->import($_POST);
					$this->objects[0]->push();
					$this->action->set_state('completed');
				} catch(Exception $e){
					$this->errors[] = $e;
					$this->action->set_state('failed');
				}
			}
		}

		if($this->action == 'show' || $this->action == 'edit' || $this->action == 'delete'){
			try {
				$this->objects[0] = new $model();
				$this->objects[0]->pull($this->params->identifier);
				$this->action->set_state('ready');
			} catch(Exception $e){
				$this->errors[] = $e;
			}

			if($this->action == 'edit' && $_POST){
				try {
					$this->objects[0]->import($_POST);
					$this->objects[0]->push();
					$this->action->set_state('completed');
				} catch(Exception $e){
					$this->errors[] = $e;
					$this->action->set_state('failed');
				}
			} else if($this->action == 'delete' && $_POST){
				try {
					$this->objects[0]->delete();
					$this->action->set_state('completed');
				} catch(Exception $e){
					$this->errors[] = $e;
					$this->action->set_state('failed');
				}
			}
		}

		if($this->action == 'list'){
			if($this->params->page == null){
				$limit = $this->params->amount;
				$offset = null;
			} else {
				$count = $model::count();
				$this->params->total = $count;

				if($count == 0){
					$this->objects = [];
					// error
				} else {
					$limit = $this->params->amount;
					$offset = $this->params->amount * ($this->params->page - 1);

					$last_page = ceil($count / $this->params->amount);

					if($this->params->page > $last_page || $this->params->page == 0){
						// error page not found
					}
				}
			}

			try {
				$this->objects = $model::pull_all($limit, $offset);
				$this->action->set_state('ready');
			} catch(Exception $e){
				$this->objects = [];
				// error
			}
		}
	}

	public function process() {
		$objs = $this->objects;
		$this->objects = [];

		foreach($objs as $key => &$obj){
			$this->objects[$key] = $obj->export();
		}
	}

	public function error($code) {
		return $this->errors[$code] ?? null;
	}

	public function success($action) {
		return $this->success[$action] ?? null;
	}
}
?>
