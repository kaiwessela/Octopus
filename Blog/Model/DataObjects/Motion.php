<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataTypes\Timestamp;
use \Blog\Model\DataTypes\MarkdownContent;
use \Blog\Model\DataObjects\Media\Application;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;

class Motion extends DataObject {
	public string 			$title;
	public ?MarkdownContent $description;
	public ?Application		$document;
	public Timestamp		$timestamp;
	public string 			$status;
	public ?array 			$votes;

#	@inherited
#	public string $id;
#	public string $longid;
#
#	public ?int $count;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'title' => '.{0,80}',
		'description' => MarkdownContent::class,
		'document' => Application::class,
		'timestamp' => Timestamp::class,
		'status' => '.{0,20}',
		'votes' => 'custom'
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->require_empty();

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['motion_id'];
		$this->longid = $row['motion_longid'];
		$this->title = $row['motion_title'];
		$this->status = $row['motion_status'];

		$this->description = empty($row['motion_description'])
			? null : new MarkdownContent($row['motion_description']);

		$this->document = empty($row['medium_id']) ? null : new Application();
		$this->document?->load($data);

		$this->timestamp = empty($row['motion_timestamp'])
			? null : new Timestamp($row['motion_timestamp']);

		$this->votes = empty($row['motion_votes'])
			? null : json_decode($row['motion_votes'], true, 512, \JSON_THROW_ON_ERROR);

		$this->set_not_new();
		$this->set_not_empty();
	}


	protected function import_custom(string $property, array $data) : void {
		if($property != 'votes'){
			return;
		}

		$value = $data['votes'];

		if(!is_array($value)){
			$this->votes = null;
			return;
		}

		$this->votes = [];

		foreach($value as $v){
			if(empty($v['party'])){
				throw new MissingValueException('party', 'string <= 30');
			} else if(!is_string($v['party']) || strlen($v['party']) > 30){
				throw new IllegalValueException('party', $v['party'], 'string <= 30');
			}

			if(empty($v['amount'])){
				throw new MissingValueException('amount', 'int');
			} else if(!is_numeric($v['amount'])){
				throw new IllegalValueException('amount', $v['amount'], 'int');
			}

			if(!in_array($v['vote'], ['yes', 'no', 'abstention'])){
				throw new IllegalValueException('vote', $v['vote'], '(yes|no|abstention)');
			}

			$this->votes[] = [
				'party' => $v['party'],
				'vote' => $v['vote'],
				'amount' => $v['amount']
			];
		}
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'description' => (string) $this->description,
			'timestamp' => (string) $this->timestamp,
			'status' => $this->status,
			'votes' => json_encode($this->votes, \JSON_THROW_ON_ERROR),
			'document_id' => $this->document?->id
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM motions
LEFT JOIN media ON medium_id = motion_document_id 
WHERE motion_id = :id OR motion_longid = :id
SQL; #---|


	const COUNT_QUERY = null;

	const INSERT_QUERY = <<<SQL
INSERT INTO motions (
	motion_id,
	motion_longid,
	motion_title,
	motion_description,
	motion_timestamp,
	motion_status,
	motion_votes,
	motion_document_id
) VALUES (
	:id,
	:longid,
	:title,
	:description,
	:timestamp,
	:status,
	:votes,
	:document_id
)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE motions SET
	motion_title = :title,
	motion_description = :description,
	motion_timestamp = :timestamp,
	motion_status = :status,
	motion_votes = :votes,
	motion_document_id = :document_id
WHERE motion_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM motions
WHERE motion_id = :id
SQL; #---|

}
?>
