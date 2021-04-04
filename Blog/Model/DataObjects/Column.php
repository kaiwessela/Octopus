<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\Traits\Paginatable;
use \Blog\Model\DataObjects\Lists\PostList;
use \Blog\Model\DataObjects\Relations\Lists\PostColumnRelationList;
use \Blog\Model\DataTypes\MarkdownContent;

class Column extends DataObject {
	public string 					$name;
	public ?MarkdownContent			$description;
	public ?PostColumnRelationList 	$postrelations;

#	@inherited
#	public string $id;
#	public string $longid;
#
#	public ?int $count;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;

	use Paginatable;
	const PAGINATABLE = true;

	const PROPERTIES = [
		'name' => '.{1,60}',
		'description' => MarkdownContent::class,
		'postrelations' => PostColumnRelationList::class
	];

	const PSEUDOLISTS = [
		'posts' => [PostList::class, 'postrelations']
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->require_empty();

		if(is_array($data[0])){
			$row = $data[0];
			$this->count = null;
		} else {
			$row = $data;

			if($norecursion){
				$this->count = null;
			} else {
				$this->count = (empty($row['postcolumnrelation_id'])) ? 0 : (int) $row['count'];
			}
		}

		$this->id = $row['column_id'];
		$this->longid = $row['column_longid'];
		$this->name = $row['column_name'];

		$this->description = empty($row['column_description'])
		? null : new MarkdownContent($row['column_description']);

		$this->postrelations = null;

		$this->set_not_new();
		$this->set_not_empty();
	}


	public function load_relations(array $data) : void {
		$this->require_not_empty();
		$this->require_not_new();

		$this->postrelations = (empty($data)) ? null : new PostColumnRelationList();
		$this->postrelations?->load($data, $this);
	}


	protected function db_export() : array {
		$export = [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description
		];

		if($this->is_new()){
			$export['longid'] = $this->longid;
		}

		return $export;
	}


	public function amount() : int {
		return $this->postrelations?->amount() ?? 0;
	}


	const PULL_QUERY = <<<SQL
SELECT *, COUNT(*) AS 'count' FROM columns
LEFT JOIN postcolumnrelations ON postcolumnrelation_column_id = column_id
WHERE column_id = :id OR column_longid = :id
SQL; #---|


	const PULL_OBJECTS_QUERY = <<<SQL
SELECT * FROM postcolumnrelations
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN media ON medium_id = post_image_id
WHERE postcolumnrelation_column_id = :id
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


	const DELETE_QUERY = <<<SQL
DELETE FROM columns WHERE column_id = :id
SQL; #---|

}
?>
