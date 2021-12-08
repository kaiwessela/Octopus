<?php


class IdentifierCondition {


	function __construct(PropertyDefinition $property, string|int|float $value) {
		if(!$property->type_is('identifier')){
			// Exception
		}

		// WHERE {$property->parent(?)::DB_TABLE}.{$property->name} = :{$property->parent::DB_PREFIX}_{$property->name}
		// values += ['{$property->parent::DB_PREFIX}_{$property->name}' => $value]

	}
}
?>
