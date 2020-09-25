<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Pagination;
use Parsedown;


class PostController extends Controller {
	const MODEL = 'Post';

	/* @inherited
	const MODEL;

	private $params;
	private $models;

	public $objects;
	public $errors;
	*/

	public $pagination;


	public function process() {
		$objs = $this->objects;
		$this->objects = [];

		foreach($objs as $key => $obj){
			$this->objects[$key] = $obj->export();
			$this->objects[$key]->parsed_content = Parsedown::instance()->text($obj->content);

			if($this->objects[$key]->image){
				$this->objects[$key]->picture = new Picture($obj->image);
			} else {
				$this->objects[$key]->picture = null;
			}
		}

		if($this->action == 'list'){
			$this->pagination = new Pagination($this->params->page, $this->params->amount, $this->params->total);
		}
	}
}
?>
