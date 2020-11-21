<?php
namespace Blog\Model\DataObjects\Relations\Lists;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;
use \Blog\Model\Exceptions\InputFailedException;

class PersonGroupRelationList extends DataObjectRelationList {

#	@inherited
#	public $container;
#	public $relations;
#
#	private $insertions;
#	private $deletions;
#	private $updates;


	public function import($data, DataObject $object) {
		$errors = new InputFailedException();

		foreach($data as $key => $value){
			$relation = new PersonGroupRelation();

			if($value['action'] == 'new'){
				$relation->generate($object);
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}

	protected function get_relation_prototype() {
		return new PersonGroupRelation();
	}
}
?>
