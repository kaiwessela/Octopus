<?php # Person.php 2021-10-04 beta
namespace Blog\Modules\Persons;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\Media\Image;
use \Blog\Modules\Persons\PersonGroupRelationList;
use \Blog\Modules\Persons\Groups\GroupList;

class Person extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 					$name;
	protected ?MarkdownContent 			$profile;
	protected ?Image 					$image;
	protected ?PersonGroupRelationList 	$grouprelations;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'profile' => MarkdownContent::class,
		'image' => Image::class,
		'grouprelations' => PersonGroupRelationList::class
	];

	const RELATIONLIST_EXTRACTS = [ // TODO
		'groups' => [GroupList::class, 'grouprelations']
	];


	const DB_PREFIX = 'person';


	const PULL_QUERY = <<<SQL
SELECT * FROM persons
LEFT JOIN media ON medium_id = person_image_id
LEFT JOIN persongrouprelations ON persongrouprelation_person_id = person_id
LEFT JOIN groups ON group_id = persongrouprelation_group_id
WHERE person_id = :id OR person_longid = :id
SQL; #---|

	const INSERT_QUERY = <<<SQL
INSERT INTO persons (person_id, person_longid, person_name, person_image_id)
VALUES (:id, :longid, :name, :image_id)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE persons SET
	person_name = :name,
	person_image_id = :image_id
WHERE person_id = :id
SQL; #---|

	const DELETE_QUERY = 'DELETE FROM persons WHERE person_id = :id';

}
?>
