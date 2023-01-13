<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Core\Model\Database\Requests\Joinable;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Relationship;

# Join creates an SQL statement to join a table to the main table of a select request. The SQL JOIN operation is
# actually not a request and cannot be performed alone, but rather a part of a SELECT request. Nevertheless, this class
# is a child of Request and for the most part behaves like a real request. This anomaly is due to the principle that
# every object has its own Request.
#
# Both SelectRequests and Joins can contain other joins (they are Joinables), so multiple joins can be chained together.
#
# The query computed on resolve() is not an SQL query, but rather an SQL statement that becomes part of the query of the
# SelectRequest that contains this Join. It looks like this:
# LEFT JOIN table ON native_column = foreign_column
#
# The search condition, which determines which rows to join together, is made up of two attributes/columns:
# The native attribute must be an attribute of the joined object ($object).
# The foreign attribute must be an attribute of the object this is joined to ($object of the request containing this).
#
# There are two dimensionalities that a Join can have: attaching and extending;
# An attaching join is used when an EntityAttribute joins the Entity it references by its identifier. There is only one
# object joined, so that its columns are in the same row of the main object's columns. (one-to-one relationship)
# An extending join is used when a RelationshipAttribute joins all the Relationships that reference the main Entity, so
# that there can be multiple result rows. (many-to-many relationship)
# In practice, the question is: does the main object reference the joined object (forward/attaching)
# or does the joined object reference the main object (reverse/extending).
#
# This class is a child of Joinable. See there for further documentation.
# This class is a child of Request. See there for further documentation.

final class Join extends Joinable {
	protected Attribute $native_attribute; # The attribute belonging to this object.
	protected Attribute $foreign_attribute; # The attribute belonging to the object joining this.


	# Initialize the SelectRequest and provide its object and the attributes making up its search condition.
	function __construct(Entity|Relationship $object, Attribute $native_attribute, Attribute $foreign_attribute) {
		parent::__construct($object); # Use the construct function of Request.

		$this->joins = [];
		$this->order = [];

		# determine the join direction. see explaination above on how exactly this works.
		if($native_attribute instanceof IdentifierAttribute && $foreign_attribute instanceof EntityReference){
			$this->is_expanding = false;
		} else if($native_attribute instanceof EntityReference && $foreign_attribute instanceof IdentifierAttribute){
			$this->is_expanding = true;
		} else {
			throw new Exception('The attributes must consist of an EntityAttribute and an IdentifierAttribute');
		}

		if(!$this->object->has_attribute($native_attribute)){
			throw new Exception('Native Attribute must be part of the joined object.');
		}

		if($this->object->has_attribute($foreign_attribute)){
			throw new Exception('Foreign Attribute must not be part of the joined object.');
		}

		$this->native_attribute = $native_attribute;
		$this->foreign_attribute = $foreign_attribute;
	}


	# Compute the join clause and append the clauses of all joins this contains.
	final protected function resolve() : void {
		$native_col = $this->native_attribute->get_prefixed_db_column();
		$foreign_col = $this->foreign_attribute->get_prefixed_db_column();

		if($this->object->get_db_table() !== $this->object->get_prefixed_db_table()){
			$this->query = "LEFT JOIN `{$this->object->get_db_table()}` AS `{$this->object->get_prefixed_db_table()}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		} else {
			$this->query = "LEFT JOIN `{$this->object->get_db_table()}` ON {$native_col} = {$foreign_col}".PHP_EOL;
		}

		$this->query .= $this->resolve_joins();
	}


	# Return the foreign attribute's name, which is used as key in the $joins array of the parent request.
	final public function get_identifier() : string {
		return $this->foreign_attribute->get_name();
	}
}
?>
