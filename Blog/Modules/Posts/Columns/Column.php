<<?php # Person.php 2021-10-04 beta
namespace Blog\Modules\Posts\Columns;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\Posts\PostList;
use \Blog\Modules\Posts\PostColumnRelationList;

class Column extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	public string 					$name;
	public ?MarkdownContent			$description;
	public ?PostColumnRelationList 	$postrelations;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'description' => MarkdownContent::class,
		'postrelations' => PostColumnRelationList::class
	];

	const RELATIONLIST_EXTRACTS = [ // TODO
		'posts' => [PostList::class, 'postrelations']
	];


	const DB_PREFIX = 'column';


	const PULL_QUERY = <<<SQL
SELECT * FROM columns
LEFT JOIN postcolumnrelations ON postcolumnrelation_column_id = column_id
WHERE column_id = :id OR column_longid = :id
SQL; #---|

	const PULL_OBJECTS_QUERY = <<<SQL
SELECT * FROM postcolumnrelations
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN media ON medium_id = post_image_id
WHERE postcolumnrelation_column_id = :id
ORDER BY post_timestamp DESC
SQL; #---|

	const INSERT_QUERY = <<<SQL
INSERT INTO columns (column_id, column_longid, column_name, column_description)
VALUES (:id, :longid, :name, :description)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE columns SET
	column_name = :name,
	column_description = :description
WHERE column_id = :id
SQL; #---|

	const DELETE_QUERY = 'DELETE FROM columns WHERE column_id = :id';

}
?>
