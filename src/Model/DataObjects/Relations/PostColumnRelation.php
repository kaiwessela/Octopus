<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Post;
use \Blog\Model\DataObjects\Column;

class PostColumnRelation extends DataObjectRelation {
#	@inherited
#	public $id;
#	public $primary_object;
#	public $secondary_object;
#
#	private $new;
#	private $empty;
#
#	const UNIQUE = true;

	const PRIMARY_ALIAS = 'post';
	const SECONDARY_ALIAS = 'column';

	const FIELDS = [];


	protected function set_object(DataObject $object) {
		if($object instanceof Post){
			$this->primary_object = $object;
			return;
		}

		if($object instanceof Column){
			$this->secondary_object = $object;
			return;
		}
	}

	protected function get_primary_prototype() {
		return new Post();
	}

	protected function get_secondary_prototype() {
		return new Column();
	}

}
?>
