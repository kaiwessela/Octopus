<?php


class EqualsCondition {
	protected PropertyDefinition|CustomColumnDefinition $column;
	protected string|int|float|null $value;

	function __construct(PropertyDefinition|CustomColumnDefinition $column, string|int|float|null $value) {

	}


	public function resolve(int $index) : array {
		return [
			'query' => $this->column->parent::DB_TABLE.'.'.$this->column->name.' = :cond_'.$number,
			'values' => ['cond_'.$number => $this->value]
		];
	}
}
