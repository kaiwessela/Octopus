<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Frontend\Web\Modules\Timestamp;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;
use InvalidArgumentException;


class PostController extends Controller {
	const MODEL = 'Post';

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
			$obj = $object->export();
			
			$obj->timestamp = new Timestamp($object->timestamp);
			$obj->content = new MarkdownContent($object->content);

			if(!$object->image->is_empty()){
				$obj->image = new Picture($object->image);
			}

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
