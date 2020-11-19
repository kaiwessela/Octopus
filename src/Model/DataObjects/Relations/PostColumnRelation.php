<?php


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

	const PRIMARY_PROTOTYPE = new Post();
	const SECONDARY_PROTOTYPE = new Column();

	const FIELDS = [];


	private function set_object(DataObject $object) {
		if($object instanceof Post){
			$this->primary_object = $object;
			return;
		}

		if($object instanceof Column){
			$this->secondary_object = $object;
			return;
		}
	}

}
?>
