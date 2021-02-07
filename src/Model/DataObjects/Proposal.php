<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataTypes\Timestamp;
use \Blog\Model\DataTypes\MarkdownContent;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;

class Proposal extends DataObject {
	public string 			$title;
	public ?MarkdownContent $description;
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
		'timestamp' => Timestamp::class,
		'status' => '.{0,20}',
		'votes' => 'custom'
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->req('empty');

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['proposal_id'];
		$this->longid = $row['proposal_longid'];
		$this->title = $row['proposal_title'];
		$this->status = $row['proposal_status'];

		$this->description = empty($row['proposal_description'])
			? null : new MarkdownContent($row['proposal_description']);

		$this->timestamp = empty($row['proposal_timestamp'])
			? null : new Timestamp($row['proposal_timestamp']);

		$this->votes = empty($row['proposal_votes'])
			? null : json_decode($row['proposal_votes'], true, 512, \JSON_THROW_ON_ERROR);

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function import_custom(string $property, array $data) : void {
		if($property != 'votes'){
			return;
		}

		$value = $data['votes'];

		if(!is_array($value)){
			$votes = null;
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

			if(empty($v['vote'])){
				$vt = null;
			} else if($v['vote'] === 'false') {
				$vt = false;
			} else {
				$vt = (bool) $v['vote'];
			}

			$this->votes[] = [
				'party' => $v['party'],
				'vote' => $vt,
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
			'votes' => json_encode($this->votes, \JSON_THROW_ON_ERROR)
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM proposals
WHERE proposal_id = :id OR proposal_longid = :id
SQL; #---|


	const COUNT_QUERY = null;

	const INSERT_QUERY = <<<SQL
INSERT INTO proposals (
	proposal_id,
	proposal_longid,
	proposal_title,
	proposal_description,
	proposal_timestamp,
	proposal_status,
	proposal_votes
) VALUES (
	:id,
	:longid,
	:title,
	:description,
	:timestamp,
	:status,
	:votes
)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE proposals SET
	proposal_title = :title,
	proposal_description = :description,
	proposal_timestamp = :timestamp,
	proposal_status = :status,
	proposal_votes = :votes
WHERE proposal_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM proposals
WHERE proposal_id = :id
SQL; #---|

}
?>
