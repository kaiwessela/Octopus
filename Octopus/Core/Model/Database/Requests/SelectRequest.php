<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Database\Condition;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Octopus\Core\Model\Database\Request;
use Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;
use Octopus\Core\Model\Database\Requests\Joinable;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Relationship;

# SelectRequest creates an SQL query for a SELECT operation, used to retrieve one or multiple rows (= objects) from the
# database.
# It creates both a query to retrieve the object data and a query to only count the amount of objects that could be
# retrieved using the provided object, attributes and conditions (COUNT).
# All columns reflecting attributes provided to the request will be retrieved.
# Joinable attributes can be included using a JoinRequest. SelectRequest is able to handle convoluted JoinRequests,
# which are JoinRequests whose result can contain more than one row, thus resulting in multiple, partially duplicate
# rows in the final, combined result, as is the case with many-to-many relationships.
# Any kind of condition can be provided to specify which rows to select.
# The number of rows/objects to select can be limited by setting a limit. Rows can be skipped by setting an offset.
# The resulting rows can be sorted by setting one or more order attributes, the first one being the most significant.
#
# This class is a child of Request. See there for further documentation.
# This class uses the Joinable trait to share functions with JoinRequest.


# An example for a convoluted select request:
#
# Suppose you have two tables called "books" and "authors":
#
# table Books: isbn, title
# table Authors: id, name
#
# You want to assign books to authors and vice versa, but you know that each book can have multiple authors and each
# author can write multiple books. How do you do that?
#
# This is a classic constellation called a many-to-many relationship: You have many books, each having many authors
# and vice versa. But how do you store all the authors of a book in the database? You cannot simply use a column and
# write multiple author ids to it, or at least it would be unefficient. Instead, you use a separate table storing these
# relationships: a so-called junction table which you call BookAuthors.
#
# table BookAuthors: id, author_id, book_isbn;
#
# So for every row in Authors, there exists at least one column in BookAuthors and one column in Books. But there can
# also be more. So if you want to select an author including all their books, you need to join BookAutors and Books.
# Your result might be the following:
#
# authors.id  authors.name  bookauthors.id  bookauthors.author_id  bookauthors.book_isbn  books.isbn  books.title
# 001         Homer         1               001                    9-000-001              9-000-001   Odyssey
# 001         Homer         1               001                    9-000-002              9-000-002   Ilias
#
# So although you only selected one author, Homer, the result contains two rows, one for each of his books, where the
# columns from the author table are simply copied across all columns.
# This is the behavior we want, but it can pose problems to us: If we want to count multiple authors while still
# include their books, for example because we want to count all authors who wrote a book having a certain title,
# we cannot simply count the columns, because there could be more columns in the result than authors.
# We have to find a way around this, which is using a more complicated and resource-intensive query than for
# simple requests, so we only want to use that more complicated query when it is really necessary. This class and
# also JoinRequest and Joinable to a significant part consist of code to detect and deal with cases like this,
# which i decided to call "convoluted requests". (See JoinRequest and Joinable for more info on that.)

final class SelectRequest extends Request {
	use Joinable;

	protected array $joins; # The JoinRequests that are executed together with this.
	protected ?int $limit; # The amount of rows to select (for SQL LIMIT statement).
	protected ?int $offset; # The amount of rows to be skipped (for SQL OFFSET statement).
	protected array $order; # The attributes/columns to sort the rows by (for SQL ORDER BY statement).
	protected ?Condition $condition; # the Condition determining which rows to select. Multiple conditions can be linked
									# together using AndOp or OrOp.

	protected string $count_query; # Caches the SQL count query computed by resolve().


	# Initialize the SelectRequest and provide its object.
	function __construct(Entity|Relationship $object) {
		parent::__construct($object); # Use the construct function of Request.

		$this->joins = [];
		$this->limit = null;
		$this->offset = null;
		$this->order = [];
		$this->condition = null;
	}


	# --> Joinable:
	# final public function add_join(JoinRequest $request) : void;
	# final public function add_order(Attribute $by, string $direction, int $significance) : void;


	# Set the limit and offset, determining how many rows to select and how many to skip.
	final public function set_limit(?int $limit, ?int $offset = null) : void {
		if(is_int($limit) && $limit <= 0){ # limit must be either null or a positive integer.
			throw new Exception('limit cannot be negative or zero.');
		}

		$this->limit = $limit;

		if(is_int($offset) && $offset < 0){ # offset must be either null or a non-negative integer.
			throw new Exception('offset cannot be negative.');
		}

		$this->offset = $offset;
	}


	# Set the condition determining which rows/objects to select.
	final public function set_condition(?Condition $condition) : void {
		$this->condition = $condition;
	}


	# Compute the select and count queries.
	final protected function resolve() : void {
		if(empty($this->attributes)){ # if there are no columns to select, an EmptyResultException is thrown.
			throw new EmptyRequestException($this);
		}

		# assemble the columns by collecting the attributes of both this request and all its joins.
		$columns = [];
		foreach($this->collect_attributes() as $attribute){
			$columns[] = "	{$attribute->get_prefixed_db_column()} AS `{$attribute->get_result_column()}`";
		}

		$columns_str = implode(','.PHP_EOL, $columns);

		# resolve the condition (WHERE statement).
		if(isset($this->condition)){
			$where = "WHERE {$this->condition->get_query()}".PHP_EOL;
			$this->values = $this->condition->get_values(); # the condition values are the only ones sent.
		} else {
			$where = '';
		}
		
		# resolve all other query elements.
		$joins = $this->resolve_joins();
		$order = $this->resolve_order();
		$limit = $this->resolve_limit();

		# If the request is convoluted (see above for what that means), a more complicated query has to be used that
		# allows to use limit and offset despite the fact that some objects span across multiple rows (which would
		# double-count them because LIMIT and OFFSET are applied on the rows, not the objects.
		#
		# If this request only selects a single object or does not limit the results, convolution is a problem for the
		# count query only, as limit does not apply for the select query and is simply being ignored by resolve_limit().

		if($this->is_convoluted() && !(is_null($this->limit) || $this->selects_single_object())){ # complicated select.
			$this->query =
			#
#<-----------
<<<"SQL"
SELECT
{$columns_str}
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
#----------->
			#
			#
		} else { # use the simple select query.
			$this->query =
			#
#<-----------
<<<"SQL"
SELECT
{$columns_str}
FROM `{$this->object->get_db_table()}` AS `{$this->object->get_prefixed_db_table()}`
{$joins}
{$where}
{$order}
{$limit}
SQL;
#----------->
			#
			#
		}
		
		if($this->is_convoluted()){ # use the complicated count query.
			$this->count_query = 
			#
#<-----------
<<<"SQL"
SELECT
COUNT(
	DISTINCT `{$this->object->get_main_identifier_attribute()->get_prefixed_db_column()}`
) AS `total`
FROM `{$this->object->get_db_table()}`
{$joins}
{$where}
SQL;
#----------->
			#
			#
		} else { # use the simple count query.
			$this->count_query = "SELECT COUNT(*) AS `total` FROM `{$this->object->get_db_table()}` {$joins} {$where}";
		}
	}


	# Resolve the order clauses by collecting them and turning them into an SQL ORDER BY clause.
	protected function resolve_order() : string {
		$orders = $this->collect_order_clauses();

		if(empty($orders)){
			return '';
		}

		$order_strings = [];
		foreach($orders as $order){
			$order_strings[] = $order->get_query();
		}

		return 'ORDER BY '.implode(', ', $order_strings).PHP_EOL;
	}


	# Resolve the limit statement. if there is no limit set or the condition ensures that only one object is selected,
	# simply ignore limit.
	protected function resolve_limit() : string {
		if(isset($this->limit) && !$this->selects_single_object()){
			$limit = "LIMIT {$this->limit}";

			# offset cannot be applied without a limit.
			if(isset($this->offset)){
				$limit .= " OFFSET {$this->offset}";
			}

			return $limit.PHP_EOL;
		} else {
			return '';
		}
	}


	# Return the computed SQL count query.
	# If it has not been computed yet, do that first by calling resolve().
	final public function get_count_query() : string {
		if(!$this->is_resolved()){
			$this->resolve();
		}

		return $this->count_query;
	}


	# Return whether the condition limits the result to a single object (because it is selected by its identifier).
	final public function selects_single_object() : bool {
		return $this->condition instanceof IdentifierEquals && $this->object->has_attribute($this->condition->get_attribute());
	}
}
?>
