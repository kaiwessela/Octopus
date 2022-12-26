<?php
namespace Octopus\Core\Model\Attributes;

trait Pullable {

	final public function is_pullable() : bool {
		return true;
	}


	final public function get_db_column() : string {
		return $this->get_name();
	}


	final public function get_prefixed_db_column() : string {
		return "`{$this->get_prefixed_db_table()}`.`{$this->get_db_column()}`";
	}


	final public function get_result_column() : string {
		return "{$this->get_prefixed_db_table()}.{$this->get_db_column()}";
	}


	abstract public function get_push_value() : null|string|int|float;
}
?>
