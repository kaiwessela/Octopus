<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\SelectAndJoin;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use \Octopus\Core\Model\Attributes\Attribute;
use Exception;

// TODO explainations

final class SelectRequest extends Request {
	# inherited from Request:
	# protected Entity|Relationship $object;
	# protected array $attributes;
	# protected string $query;
	# protected ?array $values;

	protected string $count_query;

	protected ?int $limit;
	protected ?int $offset;
	protected array $order;
	protected ?Condition $condition;

	use SelectAndJoin;

	# required by SelectAndJoin
	protected array $columns;
	protected array $joins;


	# ---> Request:
	# final public function add(Attribute $attribute) : void;
	# final public function remove(Attribute $attribute) : void;
	# final public function get_query() : string;
	# final public function get_values() : array;
	# final public function set_values(array $values) : void;
	# final public function is_resolved() : bool;


	function __construct(Entity|Relationship $object) {
		parent::__construct($object);

		$this->limit = null;
		$this->offset = null;
		$this->order = [];
		$this->condition = null;

		$this->columns = [];
		$this->joins = [];
	}


	public function set_limit(?int $limit, ?int $offset = null) : void {
		if(is_int($limit) && $limit <= 0){
			throw new Exception('limit cannot be negative or zero.');
		}

		$this->limit = $limit;

		if(is_int($offset) && $offset < 0){
			throw new Exception('offset cannot be negative.');
		}

		$this->offset = $offset;
	}


	public function set_order(array $order = []) : void {
		foreach($order as $ord){
			if(!is_array($ord)){
				throw new Exception();
			}

			if(!$ord[0] instanceof Attribute){
				throw new Exception();
			}

			if($ord[1] !== 'ASC' && $ord[1] !== 'DESC'){
				throw new Exception();
			}
		}
	}


	protected function resolve() : void {
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		$columns = $this->resolve_columns();
		$joins = $this->resolve_joins();
		$where = $this->resolve_condition();
		$order = $this->resolve_order();
		$limit = $this->resolve_limit();

		if(!$this->is_convoluted()){ // unidimensional
			$this->query = <<<"SQL"
SELECT
{$columns}
FROM `{$this->object->get_db_table()}` AS `{$this->object->get_prefixed_db_table()}`
{$joins}
{$where}
{$order}
{$limit}
SQL;


			$this->count_query = "SELECT COUNT(*) AS `total` FROM `{$this->object->get_db_table()}` {$joins} {$where}";

		} else { // multidimensional
			$this->query = <<<"SQL"
SELECT
{$columns}
FROM (
	SELECT DISTINCT `{$this->object->get_db_table()}`.* FROM `{$this->object->get_db_table()}`
	{$joins}
	{$where}
	{$order}
	{$limit}
) AS `{$this->object->get_prefixed_db_table()}`
{$joins}
{$where}
{$order}
SQL;


			$this->count_query = "SELECT COUNT(DISTINCT {$this->object->get_main_identifier_attribute()->get_prefixed_db_column()}) AS `total` FROM `{$this->object->get_db_table()}` {$joins} {$where}";
		}
	}


	protected function resolve_columns() : string {
		$columns = [];

		foreach($this->attributes as $attribute){
			$columns[] = static::create_column_string($attribute);
		}

		foreach($this->joins as $join){
			$columns = [...$columns, ...$join->get_columns()];
		}

		return implode(','.PHP_EOL, $columns);
	}


	protected function resolve_joins() : string {
		$join_string = '';

		foreach($this->joins as $join){
			$join_string .= $join->get_query().PHP_EOL;
		}

		return $join_string;
	}


	protected function resolve_condition() : string {
		if(isset($this->condition)){
			$condition = "WHERE {$this->condition->get_query()}".PHP_EOL;
			$this->values = $this->condition->get_values();
			return $condition;
		} else {
			return '';
		}
	}


	protected function resolve_order() : string {
		if(empty($this->order)){
			return '';
		}

		$orders = [];
		foreach($this->order as $ord){
			$orders[] = "{$ord[0]->get_prefixed_db_column()} {$ord[1]}";
		}

		return 'ORDER BY '.implode(', ', $orders).PHP_EOL;
	}


	protected function resolve_limit() : string {
		if(isset($this->limit)){
			$limit = "LIMIT {$this->limit}";

			if(isset($this->offset)){
				$limit .= " OFFSET {$this->offset}";
			}

			return $limit.PHP_EOL;
		} else {
			return '';
		}
	}


	final public function get_count_query() : string {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->count_query;
	}


	final public function set_condition(?Condition $condition) : void {
		$this->condition = $condition;
	}


	final public function is_convoluted() : bool {
		if($this->has_unique_identifier($this->condition)){
			return false;
		}

		foreach($this->joins as $join){
			if($join->is_convoluted()){
				return true;
			}
		}

		return false;
	}
}
?>
