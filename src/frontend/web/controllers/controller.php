<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Exceptions\InvalidParameterException;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Frontend\Web\Modules\Pagination;

/*# IDEA:

INPUT: [
	mode: 'multi' | 'single',

	#for mode=multi
	amount: int(>0)
	page: int(>0)

	#for mode=single
	identifier: string, required
]

OUTPUT: [
	#for mode=multi
	pagination

	objects: [
		[
			…,
			parsed_content,
			picture
		]
	]
]


*/# ---

abstract class Controller {
	const MODEL = null;

	protected $params;
	protected $models;

	public $objects;
	public $errors;


	function __construct() {
		$this->params = (object) [];
	}

	public function prepare($parameters) {
		$this->prepare_mode($parameters);

		if($this->params->mode == 'multi'){
			$this->prepare_amount($parameters);
			$this->prepare_page($parameters);
		}

		if($this->params->mode == 'single'){
			$this->prepare_identifier($parameters);
		}
	}

	public function execute() {
		$model = '\Blog\Backend\Models\\' . $this::MODEL;

		if($this->params->mode == 'single'){
			try {
				$this->models[0] = new $model();
				$this->models[0]->pull($this->params->identifier);
			} catch(EmptyResultException $e){
				$this->models = [];
				$this->errors['404'] = true;
			}

			return;
		}

		if($this->params->page == null){
			$limit = $this->params->amount;
			$offset = null;
		} else {
			$count = $model::count();
			if($count == 0){
				$this->models = [];
				$this->errors['404'] = true;

				return;
			}

			$this->pagination = new Pagination($count, $this->params->page, $this->params->amount);
			$this->pagination->load_items();

			if(!$this->pagination->current_page_exists()){
				$this->models = [];
				$this->errors['404'] = true;

				return;
			}

			$limit = $this->pagination->get_object_limit();
			$offset = $this->pagination->get_object_offset();
		}

		$this->models = $model::pull_all($limit, $offset);
	}

	public function process() {
		foreach($this->models as $key => &$model){
			$this->objects[$key] = $model->export();
		}
	}

	public function error($code) {
		return $this->errors[$code] ?? null;
	}


	# prepare modules
	protected function prepare_mode($parameters) {
		if($parameters['mode'] == 'single' || $parameters['mode'] == 'multi'){
			$this->params->mode = $parameters['mode'];
		} else {
			throw new InvalidParameterException('mode', 'single|multi', $parameters);
		}
	}

	protected function prepare_amount($parameters) {
		if(is_numeric($parameters['amount']) && $parameters['amount'] > 0){
			$this->params->amount = (int) $parameters['amount'];
		} else {
			$this->params->amount = null;
		}
	}

	protected function prepare_page($parameters) {
		if(!isset($parameters['page']) || $parameters['page'] == null){
			$this->params->page = null;
		} else if(is_numeric($parameters['page']) && $parameters['page'] > 0){
			$this->params->page = $parameters['page'];
		} else if(preg_match('/^\?([0-9])?/', $parameters['page'], $matches)){
			$this->params->page = (empty($_GET[$matches[1]])) ? 1 : (int) $_GET[$matches[1]];
		} else {
			throw new InvalidParameterException('page', 'int|QueryParam', $parameters);
		}
	}

	protected function prepare_identifier($parameters) {
		if(preg_match('/^\?([0-9])?/', $parameters['identifier'], $matches)){
			$this->params->identifier = $_GET[$matches[1]];
		} else if(is_string($parameters['identifier'])){
			$this->params->identifier = $parameters['identifier'];
		} else {
			throw new InvalidParameterException('identifier', 'string|QueryParam', $parameters);
		}
	}
}
?>
