<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


class EventController extends Controller {
	const MODEL = 'Event';

	/* @inherited
	public $action;
	public $errors;

	protected $params;

	public $objects;
	*/

	public $pagination;


	public function prepare($parameters) {
		parent::prepare($parameters);

		if($this->action == 'list' && isset($parameters['pagination'])){
			$this->params->pagination = (object) $parameters['pagination'];
		}
	}

	public function process() {
		parent::process();

		if(isset($this->params->pagination)){
			try {
				$this->pagination = new Pagination($this->params->page, $this->params->amount, $this->params->total, $this->params->pagination->base_path);
			} catch(InvalidArgumentException $e){
				$this->errors[] = $e;
			}
		}
	}
}
?>
