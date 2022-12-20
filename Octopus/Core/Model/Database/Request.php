<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Relationship;
use Octopus\Core\Model\Attributes\Attribute;

# Request creates an SQL query from objects, attributes and conditions provided to it.
#
# There are (currently) five supported SQL operations, each of which is handled by a separate child class:
# - SELECT, handled by SelectRequest,
# - JOIN, which expands a SelectRequest and depends on it, handled by JoinRequest,
# - INSERT, handled by InsertRequest,
# - UPDATE, handled by UpdateRequest, and
# - DELETE, handled by DeleteRequest.
#
# All of these operations have in common that they are being applied to certain columns of a certain row in a certain
# table.
# Octopus’ basic design entails that the model is a database abstraction layer, so that the object classes (children of
# Entity and Relationship) mirror the database tables. That means that for every object class, there is a corresponding
# table in the database, with the object’s attributes each correspond to a column.
# The table is thus determined by the object, which is provided to the Request on construction and stored in $object.
# The column(s) on which the operation is applied to are determined by the attributes the object provides to the Request
# via the add() or remove() functions. They are stored in the $attributes array.
# For some operations, conditions can or have to be provided to determine which instances (or rows) the operation
# is being applied to. As their requirements differ for each operation, each request class has their own functions for
# handling conditions.
#
# After the object/table, attributes/columns and conditions/rows have been provided, Request does some syntax
# validation and computes an SQL query from this data using the resolve() function.
# The Request class does not perform any semantic validation. Its only purpose to compute syntactically correct and safe
# queries. Errors due to missing or wrong data can still occur and need to be prevented or taken care of by the object
# classes themselves.
#
# Octopus only uses prepared statements, so the SQL queries never contain actual object data, but only placeholders.
# The object data is sent separately to prevent SQL injections and increase performance. The resolve() function obtains
# all the necessary data from the attributes and conditions (using their respective functions) and creates placeholders
# in the query.
# The SQL query itself can be obtained using get_query(), the data using get_values().
#
# Important further documentation:
# The Condition class, which abstracts and handles WHERE conditions.
# The Attribute class (column names; conversion of values from database to object and back).
# The Entity/Relationship classes (table names, functions that use and create these requests).
# The DatabaseAccess class (which establishes the database connection and handles the queries).

abstract class Request {
	protected Entity|Relationship $object; # The object/table the operation is being applied to.
	protected array $attributes; # of Attribute. All the attributes/columns the operation is being applied to.
	# [$attribute->get_prefixed_db_column() => $attribute, ...]
	protected string $query; # Caches the SQL query computed by resolve().
	protected array $values; # Caches the attribute and condition data obtained and assembled by resolve().
	# [placeholder => value]


	# Initialize the Request and provide its object.
	function __construct(Entity|Relationship $object) {
		$this->object = $object;
		$this->attributes = [];
		$this->values = [];
	}


	# Add an attribute to the list of attributes the operation is being applied to.
	final public function add(Attribute $attribute) : void {
		$this->require_unresolved();

		# Check that the attribute is pullable.
		# Only pullable attributes can be added this way. Check the Attribute class for more details. An example of a
		# non-pullable attribute is the RelationshipAttribute. It has no corresponding column and can only be joined.
		if(!$attribute->is_pullable()){
			throw new Exception("The attribute «{$attribute->get_prefixed_db_column()}» is not pullable.");
		}

		# Check that the attribute belongs to the object.
		if($this->object->get_db_table() !== $attribute->get_db_table()){
			throw new Exception("The attribute «{$attribute->get_prefixed_db_column()}» does not belong to the "
				. "specified object (table «{$request->get_db_table()}»).");
		}

		$this->attributes[$attribute->get_prefixed_db_column()] = $attribute; # add the attribute to the list
	}


	# Remove an attribute from the list of attributes the operation is being applied to.
	final public function remove(Attribute $attribute) : void {
		$this->require_unresolved();

		unset($this->attributes[$attribute->get_prefixed_db_column()]);
	}


	# Compute an SQL query from the object, attributes and conditions.
	abstract protected function resolve() : void;


	# Return the computed SQL query.
	# If it has not been computed yet, do that first by calling resolve().
	final public function get_query() : string {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->query;
	}


	# Return the obtained attribute and condition data.
	# If it has not been computed yet, do that first by calling resolve().
	final public function get_values() : array {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->values;
	}


	# Return whether the Request has already been resolved.
	final public function is_resolved() : bool {
		return isset($this->query);
	}


	# Throw an exception if the request has already been resolved (and thus is no longer alterable).
	final protected function require_unresolved() : void {
		if($this->is_resolved()){
			throw new Exception('The request cannot be altered after having been resolved.');
		}
	}
}
?>
