<?php
namespace Blog\Model\DataObjects\Lists;

class Person extends DataObjectList {

#	@inherited
#	public $objects;	{alias $persons}
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'persons';


	private static function load_each($data){
		$obj = new Person();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM persons
LEFT JOIN images ON image_id = person_image_id
ORDER BY person_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM persons
SQL; #---|

}
?>
