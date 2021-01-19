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


	protected static function load_each(array $data) : Person {
		$obj = new Person();
		$obj->load_single($data);
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
