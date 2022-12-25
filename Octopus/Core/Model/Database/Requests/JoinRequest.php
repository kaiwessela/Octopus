<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Core\Model\Database\Request;
use Octopus\Core\Model\Database\Requests\Joinable;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Relationship;

# JoinRequest creates an SQL statement to join a table to the main table of a select request. The SQL JOIN operation is
# actually not a request and cannot be performed alone, but rather a part of a SELECT request. Nevertheless, this class
# is a child of Request and for the most part behaves like a real request. This anomaly is due to the principle that
# every object has its own Request.
#
# Both SelectRequests and JoinRequests can contain other JoinRequests, so that multiple joins are chained together.
#
# The query computed on resolve() is not an SQL query, but rather an SQL statement that becomes part of the query of the
# SelectRequest that contains this JoinRequest. It looks like this:
# LEFT JOIN table ON native_column = foreign_column
#
# The search condition, which determines which rows to join together, is made up of two attributes/columns:
# The native attribute must be an attribute of the joined object ($object).
# The foreign attribute must be an attribute of the object this is joined to ($object of the request containing this).
#
# The JoinRequest has a direction, which can be forward or reverse.
# A forward join is used when an EntityAttribute joins the Entity it references by its identifier. There is only one
# object joined, so that its columns are in the same row of the main object's columns. (one-to-one relationship)
# A reverse join is used when a RelationshipAttribute joins all the Relationships that reference the main Entity, so
# that there can be multiple result rows. (many-to-many relationship)
# Abstractly, the question is: does the main object reference the joined object (forward)
# or does the joined object reference the main object (reverse).
#
# This class is a child of Request. See there for further documentation.
# This class uses the Joinable trait to share functions with JoinRequest.

final class JoinRequest extends Request {
	use Joinable;
	
	protected Attribute $native_attribute; # The attribute belonging to this object.
	protected Attribute $foreign_attribute; # The attribute belonging to the object joining this.
	protected array $joins; # The JoinRequests that are executed together with this.
	protected array $order; # The attributes/columns to sort the rows by (for SQL ORDER BY statement).

	protected string $direction; # forward or reverse


	# Initialize the SelectRequest and provide its object and the attributes making up its search condition.
	function __construct(Entity|Relationship $object, Attribute $native_attribute, Attribute $foreign_attribute) {
		parent::__construct($object); # Use the construct function of Request.

		$this->joins = [];
		$this->order = [];

		# determine the join direction. see explaination above on how exactly this works.
		if($native_attribute instanceof IdentifierAttribute && $foreign_attribute instanceof EntityReference){
			$this->direction = 'forward';
		} else if($native_attribute instanceof EntityReference && $foreign_attribute instanceof IdentifierAttribute){
			$this->direction = 'reverse';
		} else {
			throw new Exception('The attributes must consist of an EntityAttribute and an IdentifierAttribute');
		}

		if($native_attribute->get_prefixed_db_table() !== $this->object->get_prefixed_db_table()){
			throw new Exception('Native Attribute must be part of the joined object.');
		}

		if($foreign_attribute->get_prefixed_db_table() === $this->object->get_prefixed_db_table()){
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


	# Return whether this is a forward join.
	final public function is_forward_join() : bool {
		return $this->direction === 'forward';
	}


	# Return whether this is a reverse join.
	final public function is_reverse_join() : bool {
		return $this->direction === 'reverse';
	}
}
?>
