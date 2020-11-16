<?php


class PostColumnRelation extends DataObjectRelation {

#	@inherited
#	public $id;
#	public $container;
#	public $object;
#
#	private $new;
#	private $empty;
#
#	const UNIQUE = true;

	const CONTAINER_ALIAS = 'column';
	const OBJECT_ALIAS = 'post';

	const FIELDS = [];

}
?>
