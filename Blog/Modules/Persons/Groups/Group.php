<?php # Group.php 2021-10-04 beta
namespace Blog\Modules\Persons\Groups;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\Persons\PersonGroupRelationList;
use \Blog\Modules\Persons\PersonList;

class Group extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 					$name;
	protected ?MarkdownContent 			$description;
	protected ?PersonGroupRelationList 	$personrelations;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'description' => MarkdownContent::class,
		'personrelations' => PersonGroupRelationList::class
	];

	const RELATIONLIST_EXTRACTS = [ // TODO
		'persons' => [PersonList::class, 'personrelations']
	];


	const DB_PREFIX = 'group';


	const PULL_QUERY = <<<SQL
SELECT * FROM groups
LEFT JOIN persongrouprelations ON persongrouprelation_group_id = group_id
WHERE group_id = :id OR group_longid = :id
SQL; #---|


	const PULL_OBJECTS_QUERY = <<<SQL
SELECT * FROM persongrouprelations
LEFT JOIN persons ON person_id = persongrouprelation_person_id
LEFT JOIN media ON medium_id = person_image_id
WHERE persongrouprelation_group_id = :id
ORDER BY persongrouprelation_number
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO groups (group_id, group_longid, group_name, group_description)
VALUES (:id, :longid, :name, :description)
SQL; #---|


	const UPDATE_QUERY = 'UPDATE groups SET group_name = :name, group_description = :description WHERE group_id = :id';

	const DELETE_QUERY = 'DELETE FROM groups WHERE group_id = :id';

}
?>
