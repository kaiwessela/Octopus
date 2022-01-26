<?php
namespace Octopus\Core\Model\FlowControl;
use \Octopus\Core\Model\FlowControl\Exceptions\OutOfFlowException;
use Exception;

# A Flow is a structure that helps enforcing the defined sequence of method calls in a class. For example, in an Entity,
# it would not make sense to call the push() method (that stores the entity in the database) before actually having
# loaded its values or set any of them. By defining a Flow for the class, it is possible to control in which sequence
# the methods can be called.
# A Flow consists of a set of points on which the instance can be situated. These points then are connected by steps
# that are allowed ("From point A, it is allowed to step to point B or C").
# That means, a Flow can be considered as a graph.
# When a method is called, the first thing that should be checked is whether it is allowed right now to do that step
# (using the Flow’s check_step() method). After the method was executed, the current point can be reset (using step()).
# The first point must always be "root". All other points can be named freely (the name must be a string).

class Flow {
	# for performance reasons, the internal format for a flow is different than the 'construction' format as defined
	# in __construct(); in the internal array, there is only one key-value pair for each node, with the (starting) node
	# being the key and the value being an array of all allowed ending nodes:
	# [from => [to1, to2, to3, …], …]
	private array $flow;
	private string|null $stadium;


	# @param $flow: Array of valid steps; A valid step is itself defined as an array of two strings,
	# the first one being the starting point, the second one being the ending point of the step: [[from, to], …].
	# The flow must contain at least one edge starting from the root node.
	function __construct(array $flow) {
		$this->flow = [];
		$this->stadium = 'root';
		$root_found = false; # true if an edge starting from the root node ('root') was found

		# check all edges of the proposed flow
		foreach($flow as $edge){
			list($from, $to) = $edge; # split edge in from and to

			# check formal validity
			if(!is_array($edge) || count($edge) != 2 ||!is_string($from) || !is_string($to)){
				throw new Exception("Invalid Flow Step at: " . var_export($edge));
			}

			# check for the root node being the ending point of the edge - that is not allowed
			if($to === 'root')){
				throw new Exception("Invalid Flow: Illegal Step to «root» at: " . var_export($edge));
			}

			# check whether the root node is the starting point of the edge, in which case a root edge was found
			if($from === 'root'){
				$root_found = true;
			}

			# store the edge in the format explained above
			if(isset($this->flow[$from])){
				$this->flow[$from][] = $to;
			} else {
				$this->flow[$from] = [$to];
			}
		}

		# if no root edge was found, throw an error
		if(!$root_found){
			throw new Exception("Invalid Flow: No Starting Point found.");
		}
	}


	# Start the flow by stepping from the root node to the first ordinary node. If there are multiple root edges,
	# $stadium can define which one to take ($stadium then must equal the ending node of that edge). On null, the
	# first root edge will be chosen.
	public function start(?string $stadium = null) : void {
		if($this->stadium !== 'root'){ # if the flow has already been started, throw an error
			throw new OutOfFlowException($this->stadium, 'root');
		}

		if(empty($stadium)){
			$stadium = $this->flow['root'][0];
		}

		$this->step($stadium);
	}


	# Change the current stadium using a valid edge. $stadium is the ending node of that edge.
	public function step(string $stadium) : void {
		# first check whether the proposed step is currently allowed
		$this->check_step($stadium); # @throws OutOfFlowException

		$this->stadium = $stadium;
	}


	# Return whether stepping to a specific node (defined by $stadium) is allowed from the current stadium.
	# If $exception is true, an OutOfFlowException is thrown if the step is forbidden. Otherwise, the result is just
	# returned as a boolean.
	public function check_step(string $stadium, ?bool $exception = true) : bool {
		$valid = false;

		# check whether the proposed step is defined in $this->flow
		if(in_array($stadium, $this->flow[$this->stadium])){
			return true;
		} else if($exception){ # the step is not allowed. if an exception is expected, throw it, otherwise return false
			throw new OutOfFlowException($this->stadium, $stadium);
		} else {
			return false;
		}
	}


	# Return whether the current stadium equals $stadium
	public function is_at(string $stadium) : bool {
		return $this->stadium === $stadium;
	}
}
?>
