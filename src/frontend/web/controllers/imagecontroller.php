<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


class ImageController extends Controller {
	const MODEL = 'Image';

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
		$objs = $this->objects;
		$this->objects = [];

		foreach($objs as $key => $obj){
			$this->objects[$key] = $obj->export();
			$this->objects[$key]->picture = new Picture($obj);
		}

		if(isset($this->params->pagination)){
			try {
				$this->pagination = new Pagination(
					$this->params->page,
					$this->params->amount,
					$this->params->total,
					$this->params->pagination->base_path,
					$this->params->pagination->structure
				);
			} catch(InvalidArgumentException $e){
				$this->errors[] = $e;
			}
		}
	}
}
?>
