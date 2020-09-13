<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use Parsedown;


class PostController extends Controller {
	const MODEL = 'Post';

	public $errors = [
		'404' => false
	];

	/* @inherited
	const MODEL;

	private $params;
	private $models;

	public $objects;
	public $errors;
	*/

	public $pagination;


	public function process() {
		foreach($this->models as $key => &$model){
			$this->objects[$key] = $model->export();

			$this->objects[$key]->parsed_content = Parsedown::instance()->text($model->content);

			if($this->objects[$key]->image){
				$this->objects[$key]->picture = new Picture($model->image);
			} else {
				$this->objects[$key]->picture = null;
			}
		}
	}
}
?>
