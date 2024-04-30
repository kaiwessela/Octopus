<?php
namespace Octopus\Modules\BasicEntityController;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Controller\Router\ControllerCall;
use \Octopus\Core\Controller\Router\URLSubstitution;
use \Octopus\Modules\BasicEntityController\Pagination\Pagination;

class BasicEntityController extends EntityController {
	protected string $mode; # 'REST'|'FORM' (default)
	protected string $action; # 'show'|'edit'|'delete'|'list'|'new'|'empty'
	protected string $requested_action;

	public Entity|EntityList $object;

	protected array $pull_attributes;

	# for action==='show'|'edit'|'delete'
	protected ?string $identify_by;
	protected string $identifier;

	# for action==='list'
	protected ?int $amount;
	protected ?int $page;
	protected array $pull_conditions;
	protected array $order;
	protected ?string $pagination_scheme;
	public ?Pagination $pagination;


	public function load() : void {
		$this->load_mode();
		$this->load_action();

		$object_class = $this->call->get_entity_class();

		if($this->action === 'new' || $this->action === 'empty'){
			$this->create_object($object_class, list:false);
		} else if($this->action === 'list') {
			$this->create_object($object_class, list:true);
			$this->load_list_pull_parameters();
			$this->load_pull_conditions();
			$this->load_pull_order();
			$this->load_pagination_scheme();
			$this->load_pull_attributes();
		} else if($this->action === 'show' || $this->action === 'edit' || $this->action === 'delete'){
			$this->create_object($object_class, list:false);
			$this->load_single_pull_parameters();
			$this->load_pull_attributes();
			$this->load_pull_order();
		}

	}


	protected function load_mode() : void {
		if(!$this->call->has_option('mode') || $this->call->get_option('mode') === 'FORM'){
			$this->mode = 'FORM';
		} else if($this->call->get_option('mode') === 'REST'){
			$this->mode = 'REST';
		} else {
			throw new ControllerException(500, 'Route: Invalid option «mode».');
		}
	}


	protected function load_action() : void {
		$this->requested_action = $this->call->get_option('action');

		if(!in_array($this->requested_action, ['show', 'edit', 'delete', 'list', 'new'])){
			throw new ControllerException(500, 'Route: Invalid action.');
		}

		if($this->requested_action === 'list'){
			$this->action = 'list';
			$this->request->require_method('GET');
		} else if($this->mode === 'REST'){
			$this->action = $this->requested_action;
			$this->request->require_method(match($this->action){
				'show' => 'GET',
				'edit' => 'POST',
				'new' => 'PUT',
				'delete' => 'DELETE'
			});
		} else if($this->request->method_is('GET')){
			if($this->requested_action === 'new'){
				$this->action = 'empty';
			} else {
				$this->action = 'show';
			}
		} else {
			$this->action = $this->requested_action;
		}
	}


	protected function create_object(string $class, bool $list = false) : void {
		if($list){
			$this->object = $class::list($this->endpoint->get_db());
		} else {
			$this->object = new $class($this->endpoint->get_db());
		}
	}


	protected function load_single_pull_parameters() : void {
		$this->identify_by = URLSubstitution::replace($this->call->get_option('identify_by'), $this->request);

		if(!is_string($this->identify_by) && !is_null($this->identify_by)){
			throw new ControllerException(500, 'Route: Invalid option «identify_by».');
		}

		$this->identifier = URLSubstitution::replace($this->call->get_option('identifier'), $this->request, true);

		if(!is_string($this->identifier)){
			throw new ControllerException(500, 'Route: Invalid option «identifier».');
		}
	}


	protected function load_list_pull_parameters() : void {
		$amount = $this->call->get_option('amount');

		if(is_string($amount)){
			$amount = URLSubstitution::replace($amount, $this->request);
		}

		if(!is_int($amount) && !is_null($amount)){
			throw new ControllerException(500, 'Route: Invalid option «amount».'); // TODO other exception
		}

		$this->amount = $amount;

		if(isset($amount)){
			$page = $this->call->get_option('page');

			if(is_null($page)){
				$page = 1;
			} else if(is_string($page)){
				$page = URLSubstitution::replace($page, $this->request) ?? 1;
			}

			if(!is_int($page)){
				throw new ControllerException(500, 'Route: Invalid option page.'); // TODO other exception
			}
		} else {
			$page = null;
		}

		$this->page = $page;
	}


	protected function load_pull_attributes() : void {
		$attributes = $this->call->get_option('attributes');

		if(!is_array($attributes) && !is_null($attributes)){
			throw new ControllerException(500, 'Route: Invalid option «attributes».');
		}

		$this->pull_attributes = $attributes ?? [];
	}


	protected function load_pull_conditions() : void {
		$conditions = $this->call->get_option('conditions');

		if(!is_array($conditions) && !is_null($conditions)){
			throw new ControllerException(500, 'Route: Invalid option «conditions».');
		}

		$this->pull_conditions = [];

		foreach($conditions ?? [] as $attribute => $condition){
			if(is_string($condition)){
				$condition = URLSubstitution::replace($condition, $this->request);
			}

			if(!is_null($condition)){
				$this->pull_conditions[$attribute] = $condition;
			}
		}
	}


	protected function load_pull_order() : void {
		// TODO
		$this->order = $this->call->get_option('order') ?? [];
	}


	protected function load_pagination_scheme() : void {
		$this->pagination_scheme = $this->call->get_option('pagination_url_scheme') ?? URLSubstitution::backwards([
			'page' => $this->page,
			'amount' => $this->amount
		], $this->request);
	}


	public function execute() : void {
		if($this->action === 'list'){
			if(is_null($this->amount) || $this->page === 1){
				$offset = null;
			} else {
				$offset = $this->amount * ($this->page - 1);
			}

			try {
				$this->object->pull($this->amount, $offset, $this->pull_attributes, $this->pull_conditions, $this->order);
			} catch(EmptyResultException $e){}

			$this->set_status_code(200);

		} else if($this->action === 'empty'){
			$this->set_status_code(200);

		} else if($this->action === 'new'){
			try {
				$this->object->create();
				$this->object->receive_input($this->request->get_post_data());
				$this->object->push();
				$this->set_status_code(201);
			} catch(AttributeValueExceptionList $e){
				$this->set_status_code(422);
				throw new ControllerException(422, '', $e);
				return; // TODO invalid input
			} // TODO EmptyRequestException

		} else { # action==='show'|'edit'|'delete'
			try {
				$this->object->pull($this->identifier, $this->identify_by, $this->pull_attributes, $this->order);
			} catch(EmptyResultException $e){
				$this->set_status_code(404);
				throw new ControllerException(404, 'Object not found.');
			}

			if($this->action === 'show'){
				$this->set_status_code(200);
				return;

			} else if($this->action === 'edit'){
				try {
					$this->object->receive_input($this->request->get_post_data());
					$this->object->push();
					$this->set_status_code(200);
				} catch(AttributeValueExceptionList $e){
					$this->set_status_code(422);
					// echo $e->get_messages();
					throw new ControllerException(422, '', $e);
					return; // TODO invalid input
				}

			} else if($this->action === 'delete'){
				// TODO require the id to avoid accidental deletions

				$this->object->delete();

				$this->set_status_code(200);
			}
		}
	}


	public function finish() : void {
		if($this->action === 'list'){
			$this->pagination = new Pagination($this->page, $this->amount, $this->object->count_total(), $this->pagination_scheme);
		}
	}


	public function get_action() : string {
		return $this->action;
	}


	public function get_requested_action() : string {
		return $this->requested_action;
	}
}
?>
