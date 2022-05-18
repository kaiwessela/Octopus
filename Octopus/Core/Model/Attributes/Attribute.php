<?php
namespace Octopus\Core\Model\Attributes;

abstract class Attribute {
	protected ?Entity $parent;
	protected string $parent_class;
	protected string $name;
	protected ?string $db_column;
	protected mixed $value;
	protected bool $editable;
	protected bool $required;
	protected bool $edited;


	abstract public static function define() : Attribute;


	public function init(string $parent_class, string $name) : void {
		$this->parent_class = $parent_class;
		$this->name = $name;
		$this->db_column = $name;
		$this->parent = null;
	}


	public function bind(Entity $parent) : void {
		$this->parent = &$parent;
		$this->edited = false;
	}


	public function load(mixed $value) : void {

	}


	public function edit() : void {

	}


	final public function get_value() : mixed {
		return $this->value;
	}


	final public function get_push_value() : null|string|int|float {
		if($this->is_empty() && $this->is_required()){
			throw new MissingValueException($this);
		}

		return $this->return_push_value();
	}


	protected function return_push_value() : null|string|int|float {
		return $this->value;
	}


	public function store() : void {
		return;
	}


	public function erase() : void {
		return;
	}


	final public function has_been_edited() : bool {
		return $this->edited;
	}


	final public function get_name() : string {
		return $this->name;
	}


	final public function get_db_table() : string {
		return $this->parent_class::DB_TABLE;
	}


	final public function get_db_prefix() : string {
		return $this->parent_class::DB_PREFIX;
	}


	final public function get_db_column() : ?string {
		return $this->db_column;
	}


	final public function get_full_db_column() : string {
		return "{$this->get_db_table()}.{$this->get_db_column()}";
	}


	final public function get_prefixed_db_column() : string {
		return "{$this->get_db_prefix()}_{$this->get_db_column()}";
	}


	public function is_pullable() : bool {
		return true;
	}


	public function is_joinable() : bool {
		return false;
	}


	public function export() : null|string|int|float|bool|array {
		return $this->value;
	}


	public function freeze() : void {
		return;
	}


	public function is_empty() : bool {
		return $this->value === null;
	}





}
?>
