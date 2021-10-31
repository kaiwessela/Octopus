<?php # Motion.php 2021-10-04 beta
namespace Blog\Modules\Motion;
use \Blog\Core\Model\DataObject;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\DataTypes\Timestamp;
use \Blog\Modules\Media\Application;

class Motion extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 			$title;
	protected ?MarkdownContent 	$description;
	protected ?Application		$document;
	protected Timestamp			$timestamp;
	protected string 			$status;
	protected ?array 			$votes;


	const PROPERTIES = [
		'title' => '.{0,140}',
		'description' => MarkdownContent::class,
		'document' => Application::class,
		'timestamp' => Timestamp::class,
		'status' => '.{0,20}',
		'votes' => 'custom'
	];


	const DB_PREFIX = 'motion';


	protected function load_custom_property(string $name, mixed $value, ?PropertyDefinition $def = null) : void {
		if($name === 'votes'){
			if(empty($value)){
				$this->votes = null;
			} else {
				json_decode($value, true, default, \JSON_THROW_ON_ERROR);
			}
		}
	}

	protected function edit_custom_property(string $name, mixed $input, ?PropertyDefinition $def = null) : void {
		if($name === 'votes'){
			if(!is_array($input)){
				$this->$votes = null;
				return;
			}

			$this->votes = [];

			foreach($input as $index => $vote){
				if(empty($vote['party'])){
					throw new PropertyValueException($def, "Vote $index: party missing.");
				} else if(!is_string($vote['party']) || strlen($vote['party']) > 30){
					throw new PropertyValueException($def, "Vote $index: party invalid.");
				}

				if(empty($vote['amount'])){
					throw new PropertyValueException($def, "Vote $index: amount missing.");
				} else if(!is_numeric($vote['amount'])){
					throw new PropertyValueException($def, "Vote $index: amount invalid.");
				}

				if(!in_array($vote['vote'], ['yes', 'no', 'abstention'])){
					throw new PropertyValueException($def, "Vote $index: vote invalid or missing.");
				}

				$this->votes[] = [
					'party' => $vote['party'],
					'vote' => $vote['vote'],
					'amount' => $vote['amount']
				]
			}
		}
	}


	protected function get_custom_push_values(string $property) : array {
		if($property === 'votes'){
			return ['votes' => json_encode($this->votes, \JSON_THROW_ON_ERROR)];
		}
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM motions
LEFT JOIN media ON medium_id = motion_document_id
WHERE motion_id = :id OR motion_longid = :id
SQL; #---|

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

	const DELETE_QUERY = 'DELETE FROM motions WHERE motion_id = :id';

}
?>
