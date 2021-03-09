<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Person;

class PersonList extends DataObjectList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Person::class;
	const OBJECTS_ALIAS = 'persons';


	const SELECT_QUERY = <<<SQL
SELECT * FROM persons
LEFT JOIN media ON medium_id = person_image_id
ORDER BY person_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM persons
SQL; #---|

}
?>
