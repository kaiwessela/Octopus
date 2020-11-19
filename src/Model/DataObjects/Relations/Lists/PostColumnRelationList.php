<?php
namespace Blog\Model\DataObjects\Relations\Lists;

class PostColumnRelationList extends DataObjectRelationList {

#	@inherited
#	public $container;
#	public $relations;
#
#	private $insertions;
#	private $deletions;
#	private $updates;

	const RELATION_PROTOTYPE = new PostColumnRelation();
	

	public function import($data, DataObject $object) {
		$errors = new InputFailedException();

		foreach($data as $key => $value){
			$relation = new PostColumnRelation();

			if($value['action'] == 'new'){
				$relation->generate($object);
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}



}
