<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Post;
use \Blog\Model\DataObjects\Relations\PostColumnRelation;
use \Blog\Model\DataObjects\Relations\Lists\PostColumnRelationList;

class Column extends DataObject {

#					NAME				TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 	$name;			#	str			*			.{1,30}		=			=
	public ?string 	$description;	#	str						.*			=			=
	public ?array 	$posts;			#	arr[Post]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;
#
#	const IGNORE_PULL_LIMIT = false;

	const PROPERTIES = [
		'name' => '.{1,30}',
		'description' => null,
		'posts' => PostColumnRelationList::class
	];


	function __construct() {
		parent::__construct();
		$this->relationlist = new PostColumnRelationList();
	}


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);

		$relations = [];
		foreach($data as $postdata){
			if(empty($postdata['postcolumnrelation_id'])){
				continue;
			}

			$post = new Post();
			$post->load_single($postdata);
			$this->posts[] = $post;

			$relation = new PostColumnRelation();
			$relation->load($this, $post, $postdata);
			$relations[$relation->id] = $relation;
		}

		$this->relationlist->load($relations);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['column_id'];
		$this->longid = $data['column_longid'];
		$this->name = $data['column_name'];
		$this->description = $data['column_description'];

		$this->set_new(false);
		$this->set_empty(false);
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


	const PULL_QUERY = <<<SQL
SELECT * FROM columns
LEFT JOIN postcolumnrelations ON postcolumnrelation_column_id = column_id
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN images ON image_id = post_image_id
WHERE column_id = :id OR column_longid = :id
ORDER BY post_timestamp DESC
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM postcolumnrelations WHERE postcolumnrelation_column_id = :id
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO columns
	(column_id, column_longid, column_name, column_description)
VALUES
	(:id, :longid, :name, :description)
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
