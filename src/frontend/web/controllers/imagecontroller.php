<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Backend\Models\Image;
use \Blog\Frontend\Web\Modules\Pagination;


class ImageController extends Controller {
	const MODEL = 'Image';

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
			$this->objects[$key]->picture = new Picture($model);
		}
	}
}
?>
