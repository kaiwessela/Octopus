<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Post;
use \Blog\Model\DataObjects\Column;

class PostColumnRelation extends DataObjectRelation {
#	@inherited
#	public $id;
#	public $primary_object;
#	public $secondary_object;
#
#	private $new;
#	private $empty;
#
#	const UNIQUE = true;

	const PRIMARY_ALIAS = 'post';
	const SECONDARY_ALIAS = 'column';

	const FIELDS = [];


	public function load(DataObject $object1, DataObject $object2, array $data = []) : void {
		parent::load($object1, $object2, $data);

		$this->id = $data['postcolumnrelation_id'];
	}


	protected function set_object(DataObject $object) : void {
		if($object instanceof Post){
			$this->primary_object = $object;
			return;
		}

		if($object instanceof Column){
			$this->secondary_object = $object;
			return;
		}
	}

	protected function get_primary_prototype() : Post {
		return new Post();
	}

	protected function get_secondary_prototype() : Column {
		return new Column();
	}

	protected function db_export() : array {
		return [
			'id' => $this->id,
			'post_id' => $this->primary_object->id,
			'column_id' => $this->secondary_object->id
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
