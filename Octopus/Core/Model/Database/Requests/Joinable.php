<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Database\Requests\JoinRequest;
use Octopus\Core\Model\Database\Requests\SelectRequest;

# The Joinable trait defines functions that are used by all Requests that can join other requests to it, which are
# SelectRequest and JoinRequest.

# Convoluted Requests:
# A request is convoluted exactly then when it is possible that results
# for an object can be spread across multiple rows. (the rule "one row per object" no longer applies).
# If one of the request's joins is convoluted, the request itself is too.
#
# This happens when the request contains reverse joins. In practice, this can happen when the object to be selected or
# joined contains a RelationshipAttribute.
#
# Convolution becomes problematic for counting and limiting, because simply counting the rows does no longer work.
# therefore, a two-step process with a subquery has to be used:
# first, the complete query is performed (including joins, conditions, order, limits), but it is performed as a
# distinct select with only the attributes of the main object (which is the object of the SelectRequest).
# what happens is that the request is performed normally, but before counting/limiting, all join columnsare stripped and
# duplicate rows are stripped too (due to the DISTINCT), so counting/limiting works again. to get back the joins, the
# result of the subquery is then used as the table for the main request and all joins, conditions and orders are applied
# again.

# Order:
# Order directives are stored in the Request of the object that contains the attribute to sort the results by,
# using the following format for $order: $significance => [Attribute $by, string $direction], ...
#
# When resolving the main SelectRequest, the order directives are collected and chained together in a quite complicated
# way, following these steps:
# 1. For the SelectRequest and each reverse join request, merge the own order chain and the chains of all forward joins
#	below it.
# 2. sort these chains by the significance of their directives and reindex them to create an uninterrupted list.
# 3. append the default order directive of the SelectRequest resp. the reverse join requests to each respective chain.
#	the default order directive is the object's main identifier in ascending order.
# 4. glue all chains together, beginning with the SelectRequest and descending by breadth-first search.
#
# This complicated rigmarole is necessary to ensure that lower-order order directives do not disarrange the rows of the
# higher-order object, which would render the result unparseable.


trait Joinable {

	# Add a JoinRequest to join another object to this request's object.
	final public function add_join(JoinRequest $request) : void {
		$this->joins[] = $request;
	}


	# Add an attribute/a column to sort the result rows by.
	# $direction can be ASC (ascending) or DESC (descending).
	# $significance determines the order of the columns the result is sorted by. lower significance means that the
	# resulting rows are sorted by this column earlier. in practice, the significance is the index of the attribute in
	# the original order chain (set on the initial pull() function).
	final public function add_order(Attribute $by, string $direction, int $significance) : void {
		if(!array_key_exists($by->get_prefixed_db_column(), $this->attributes)){
			throw new Exception('The column to order the result by is not included in the column list.');
		}

		if(!($direction === 'ASC' || $direction === 'DESC')){
			throw new Exception('Invalid order direction.');
		}

		if($significance < 0){
			throw new Exception('Invalid order significance (must be greater than 0).');
		}

		$this->order[$significance] = [$by, $direction];
	}


	# Return an array of the attributes of this request and of all its joins.
	final protected function collect_attributes() : array {
		$attributes = $this->attributes;

		foreach($this->joins as $join){
			$attributes = [...$attributes, ...$join->collect_attributes()];
		}

		return $attributes;
	}


	# Return an array (chain) of the order directives of this request and all of its forward joins (Step 1).
	final protected function collect_order_directives_forward() : array {
		$chain = $this->order;

		foreach($this->joins as $join){
			if($join->is_forward_join()){
				$chain += $join->collect_order_directives_forward();			
			}
		}

		return $chain;
	}


	# Return an array of arrays (chainchains) of the order directives of this request and all its reverse joins, ordered
	# by the depth of the join request.
	# depth 0 (= the chain in $chainchains[0]) is the SelectRequest, depth 1 (= the chains in $chainchains[1]) are all
	# reverse joins in SelectRequests->joins, depth 2 are all reverse joins in the requests of level 1 and so on.
	final protected function collect_order_directives_reverse() : array {
		$chainchains = [];

		# If this is a SelectRequest or reverse join, then:
		# 1. merge the own order chain and the chains of all forward joins below it.
		# 2. sort these chains by the significance of their directives and reindex them to create an uninterrupted list.
		# 3. append the default order directive of this request.
		if($this instanceof SelectRequest || $this->is_reverse_join()){
			$chainchains[0] = [];
			
			$chain = $this->collect_order_directives_forward(); # step 1
			ksort($chain); # step 2 (sort by keys (= significance))
			$chain = array_values($chain); # step 2, ctd. (rekey to create an uninterrupted list)
			$chain[] = [$this->object->get_main_identifier_attribute(), 'ASC']; # step 3 (default order directive)
			$chainchains[0][0] = $chain; # write the chain to the list (as first chain on the first level)
		}

		# This is where this function becomes recursive. Repeat the process above for all joins and write the result
		# into $chains (forward joins actually just pass on the results of their own joins) and count all their levels
		# up by 1.
		# Picture it as that the tree is build from the roots up, moving the former chains down one level with each
		# step. In the end, the SelectRequest's order chain gets level 0.
		foreach($this->joins as $join){
			foreach($join->collect_order_directives_reverse() as $level => $chains){
				# IDEA:
				## if the lowest level (= the level that was just added by the last iteration of this function, in the
				## join level just below this request) contains multiple chains (that happens if this request contains 
				## multiple direct reverse joins), these order directive chains should be ordered by their length, so
				## that the longest chain precedes the others (and so on).
				## we do that because we assume that requests with more order directives are more important for the user
				## to be ordered as wished than those with less directives. The original problem is that if there are
				## multiple reverse joins on the same level, only one of them can be guaranteed to have its results
				## ordered correctly. the only real solution to this problem would be to split the request into multiple
				## independent requests.
				#if($level === 0 && count($chains) > 1){
				#	$chains_by_length = [];
				#	
				#	foreach($chains as $chain){
				#		$length = count($chain);
				#
				#		if(!isset($chains_by_length[$length])){
				#			$chains_by_length[$length] = [];
				#		}
				#
				#		$chains_by_length[$length][] = $chain;
				#	}
				#
				#	$chainchains[$level+1] = array_reverse($chains_by_length);
 				#} else {
					$chainchains[$level+1][] = $chains;
				#}
			}
		}

		return $chainchains;
	}


	# Resolve all joins and return their adjoined queries.
	final protected function resolve_joins() : string {
		$query = '';

		foreach($this->joins as $join){
			$query .= $join->get_query();
		}

		return $query;
	}

	
	# Return whether the request is convoluted. See explaination above for what exactly that means.
	final public function is_convoluted() : bool {
		$result = false;

		foreach($this->joins as $join){
			$result |= $join->is_convoluted() || $join->is_reverse_join();
		}

		return $result;
	}
}
?>
