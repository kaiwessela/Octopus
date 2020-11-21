<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Person;

class PersonList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $persons}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'persons';


	protected static function load_each($data){
		$obj = new Person();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM persons
LEFT JOIN images ON image_id = person_image_id
LEFT JOIN persongrouprelations ON persongrouprelation_person_id = person_id
LEFT JOIN groups ON group_id = persongrouprelation_group_id
ORDER BY person_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM persons
SQL; #---|

}
?>
