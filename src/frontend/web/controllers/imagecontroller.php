<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;


class ImageController extends Controller {
	const MODEL = 'Image';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process() {
		$objs = [];
		foreach($this->objects as $object){
			$obj = new Picture($object);
			$objs[] = $obj;
		}
		$this->objects = $objs;

		if(isset($this->request->custom['pagination_structure']) && $this->request->action == 'list'){
			try {
				$this->pagination = new Pagination(
					$this->request->page,
					$this->request->amount,
					$this->count,
					'base_path',
					$this->request->custom['pagination_structure']
				);
			} catch(InvalidArgumentException $e){
				$this->exceptions[] = $e;
			}
		}
	}
}
?>
