<?php
namespace Octopus\Modules\Motion;
use \Blog\Core\Model\DataObject;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\DataTypes\Timestamp;
use \Blog\Modules\Media\Application;

class Motion extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 		$title;
	protected ?MarkdownText $description;
	protected ?Application	$document;
	protected ?Timestamp	$timestamp;
	protected ?string 		$status;
	protected ?array 		$votes;


	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{0,140}',
		'description' => MarkdownText::class,
		'document' => Application::class,
		'timestamp' => Timestamp::class,
		'status' => '.{0,20}',
		'votes' => 'custom'
	];


	const DB_TABLE = 'motions';
	const DB_PREFIX = 'motion';


	protected function load_custom_attribute(AttributeDefinition $definition, array $row) : void {
		if($definition->get_name() !== 'votes'){
			return;
		}

		if(empty($row['motion_votes'])){
			$this->votes = null;
		} else {
			$this->votes = json_decode($row['motion_votes'], true, default, \JSON_THROW_ON_ERROR);
		}
	}


	protected function edit_custom_attribute(AttributeDefinition $definition, mixed $input) : void {
		if($definition->get_name() !== 'votes'){
			return;
		}

		// HIER WEITER

		if(!is_array($input)){
			$this->$votes = null;
			return;
		}

		$this->votes = [];

		foreach($input as $index => $vote){
			if(empty($vote['party'])){
				throw new PropertyValueException($definition, "Vote $index: party missing.");
			} else if(!is_string($vote['party']) || strlen($vote['party']) > 30){
				throw new PropertyValueException($definition, "Vote $index: party invalid.");
			}

			if(empty($vote['amount'])){
				throw new PropertyValueException($definition, "Vote $index: amount missing.");
			} else if(!is_numeric($vote['amount'])){
				throw new PropertyValueException($definition, "Vote $index: amount invalid.");
			}

			if(!in_array($vote['vote'], ['yes', 'no', 'abstention'])){
				throw new PropertyValueException($definition, "Vote $index: vote invalid or missing.");
			}

			$this->votes[] = [
				'party' => $vote['party'],
				'vote' => $vote['vote'],
				'amount' => $vote['amount']
			]
		}
	}


	protected function get_custom_push_values() : array {
		return ['votes' => json_encode($this->votes, \JSON_THROW_ON_ERROR)];
	}
}
?>
