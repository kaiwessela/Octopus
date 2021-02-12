<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Post;
use \Blog\Model\DataObjects\Column;
use TypeError;

class PostColumnRelation extends DataObjectRelation {
	public ?Post 	$post;
	public ?Column 	$column;

#	@inherited
#	public string $id;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;
#
#	const UNIQUE = true;

	const OBJECTS = [
		'post' => Post::class,
		'column' => Column::class
	];

	const PROPERTIES = [];


	public function generate(/*Post|Column*/ $object) : void {
		parent::generate($object);

		if($object instanceof Post){
			$this->post = &$object;
			$this->column = null;
		} else if($object instanceof Column){
			$this->column = &$object;
			$this->post = null;
		} else {
			throw new TypeError('Invalid type of $object.');
		}
	}


	public function load(array $data, /*Post|Column*/ $object) : void {
		$this->req('empty');

		if($object instanceof Post){
			$this->post = &$object;
			$this->column = new Column();
			$this->column->load($data, true);
		} else if($object instanceof Column){
			$this->column = &$object;
			$this->post = new Post();
			$this->post->load($data, true);
		} else {
			throw new TypeError('Invalid type of $object.');
		}

		$this->id = $data['postcolumnrelation_id'];

		$this->set_empty(false);
	}


	protected function db_export() : array {
		return [
			'id' => $this->id,
			'post_id' => $this->post->id,
			'column_id' => $this->column->id
		];
	}


	const INSERT_QUERY = <<<SQL
INSERT INTO postcolumnrelations (
	postcolumnrelation_id,
	postcolumnrelation_post_id,
	postcolumnrelation_column_id
) VALUES (
	:id,
	:post_id,
	:column_id
)
SQL; #---|


	const UPDATE_QUERY = null;


	const DELETE_QUERY = <<<SQL
DELETE FROM postcolumnrelations
WHERE postcolumnrelation_id = :id
SQL; #---|

}
?>
