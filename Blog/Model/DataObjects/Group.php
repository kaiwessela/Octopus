<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\Traits\Paginatable;
use \Blog\Model\DataObjects\Lists\PersonList;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;
use \Blog\Model\DataTypes\MarkdownContent;

class Group extends DataObject {
	public string 					$name;
	public ?MarkdownContent 		$description;
	public ?PersonGroupRelationList $personrelations;

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	use Paginatable;
	const PAGINATABLE = true;

	const PROPERTIES = [
		'name' => '.{1,60}',
		'description' => MarkdownContent::class,
		'personrelations' => PersonGroupRelationList::class
	];

	const PSEUDOLISTS = [
		'persons' => [PersonList::class, 'personrelations']
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
				$this->count = (empty($row['persongrouprelation_id'])) ? 0 : (int) $row['count'];
			}
		}

		$this->id = $row['group_id'];
		$this->longid = $row['group_longid'];
		$this->name = $row['group_name'];

		$this->description = empty($row['group_description'])
		? null : new MarkdownContent($row['group_description']);

		$this->personrelations = null;

		$this->set_not_new();
		$this->set_not_empty();
	}


	public function load_relations(array $data) : void {
		$this->require_not_empty();
		$this->require_not_new();

		$this->personrelations = (empty($data)) ? null : new PersonGroupRelationList();
		$this->personrelations?->load($data, $this);
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
		return $this->personrelations?->amount() ?? 0;
	}


	const PULL_QUERY = <<<SQL
SELECT *, COUNT(*) AS 'count' FROM groups
LEFT JOIN persongrouprelations ON persongrouprelation_group_id = group_id
WHERE group_id = :id OR group_longid = :id
SQL; #---|


	const PULL_OBJECTS_QUERY = <<<SQL
SELECT * FROM persongrouprelations
LEFT JOIN persons ON person_id = persongrouprelation_person_id
LEFT JOIN media ON medium_id = person_image_id
WHERE persongrouprelation_group_id = :id
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO groups (group_id, group_longid, group_name, group_description)
VALUES (:id, :longid, :name, :description)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE groups SET
	group_name = :name,
	group_description = :description
WHERE group_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM groups WHERE group_id = :id
SQL; #---|

}
?>
