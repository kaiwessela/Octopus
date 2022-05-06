<?php
namespace Octopus\Modules\BasicEntityController;
use \Octopus\Core\Controller\Controllers\EntityController;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Controller\Router\ControllerCall;
use \Octopus\Core\Controller\Router\URLSubstitution;
use \Octopus\Modules\BasicEntityController\Pagination\Pagination;

class BasicEntityController extends EntityController {
	protected string $mode; # 'REST'|'FORM' (default)
	protected string $action; # 'show'|'edit'|'delete'|'list'|'new' (|'empty')

	# for action==='show'|'edit'|'delete'
	protected ?string $identify_by;
	protected mixed $identifier;

	# for action==='list'
	protected ?int $amount;
	protected ?int $page;

	protected array $options;

	protected ?string $pagination_scheme;

	public ?Pagination $pagination;

	public ?Entity $entity;
	public ?EntityList $entities;


	public function load(Request &$request, ControllerCall $call) : void {
		if(!$call->has_option('mode') || $call->get_option('mode') === 'FORM'){
			$this->mode = 'FORM';
		} else if($call->get_option('mode') === 'REST'){
			$this->mode = 'REST';
		} else {
			throw new ControllerException(500, 'Route: Invalid option «mode».');
		}

		$action = $call->get_option('action');
		$entity_class = $call->get_entity_class();

		if($action === 'list'){
			if($this->mode === 'REST' && !$request->method_is('GET')){
				throw new ControllerException(405, 'Method not allowed.');
			}

			$list_class = $entity_class::LIST_CLASS;

			$this->entity = null;
			$this->entities = new $list_class();
			$this->action = 'list';
		} else if($action === 'new'){
			$this->entities = null;

			if($request->method_is('GET')){
				if($this->mode === 'REST'){
					throw new ControllerException(405, 'Method not allowed.');
				}

				$this->entity = null;
				$this->action = 'empty';
			} else if($request->method_is('POST')){
				$this->entity = new $entity_class();
				$this->action = 'new';
			} else {
				throw new ControllerException(405, 'Method not allowed.');
			}
		} else if($action === 'show'){
			$this->entities = null;

			if(!$request->method_is('GET')){
				throw new ControllerException(405, 'Method not allowed.');
			}

			$this->entity = new $entity_class();
			$this->action = 'show';
		} else if($action === 'edit'){
			$this->entities = null;

			if($request->method_is('GET')){
				if($this->mode === 'REST'){
					throw new ControllerException(405, 'Method not allowed.');
				}

				$this->action = 'show';
			} else if($request->method_is('POST')){
				if($this->mode === 'REST'){
					throw new ControllerException(405, 'Method not allowed.');
				}

				$this->action = 'edit';
			} else if($request->method_is('PUT')){
				$this->action = 'edit';
			} else {
				throw new ControllerException(405, 'Method not allowed.');
			}

			$this->entity = new $entity_class();
		} else if($action === 'delete'){
			$this->entities = null;

			if($request->method_is('GET')){
				if($this->mode === 'REST'){
					throw new ControllerException(405, 'Method not allowed.');
				}

				$this->action = 'show';
			} else if($request->method_is('POST')){
				if($this->mode === 'REST'){
					throw new ControllerException(405, 'Method not allowed.');
				}

				$this->action = 'delete';
			} else if($request->method_is('DELETE')){
				$this->action = 'delete';
			} else {
				throw new ControllerException(405, 'Method not allowed.');
			}

			$this->entity = new $entity_class();
		} else {
			throw new ControllerException(500, 'Route: Invalid option «action».');
		}

		if($this->action === 'show' || $this->action === 'edit' || $this->action === 'delete'){
			if($call->has_option('identify_by')){
				if(!is_string($call->get_option('identify_by'))){
					throw new ControllerException(500, 'Route: Invalid option «identify_by».');
				}

				$this->identify_by = URLSubstitution::replace($call->get_option('identify_by'), $request, force_string:true);
			} else {
				$this->identify_by = 'id';
			}

			$this->identifier = URLSubstitution::replace($call->get_option('identifier'), $request, force_string:true); // TODO validate this
		} else {
			$this->identify_by = null;
			$this->identifier = null;
		}

		if($this->action === 'list' || $this->action === 'show'){
			if($call->has_option('amount')){
				if(is_string($call->get_option('amount'))){
					$amount = URLSubstitution::replace($call->get_option('amount'), $request); // TODO validate this
				} else {
					$amount = $call->get_option('amount');
				}

				if(!is_int($amount)){
					throw new ControllerException(500, 'Route: Invalid option «amount».'); // TODO other excepiton
				}

				$this->amount = $amount;

				if($call->has_option('page')){
					if(is_string($call->get_option('page'))){
						$page = URLSubstitution::replace($call->get_option('page'), $request); // TODO validate
					} else {
						$page = $call->get_option('page');
					}

					if(!is_int($page)){
						throw new ControllerException(500, 'Route: Invalid option «page».'); // TODO other excepiton
					}

					$this->page = $page;
				} else {
					$this->page = 1;
				}
			} else {
				$this->page = null;
				$this->amount = null;
			}

			$this->pagination_scheme = $call->get_option('pagination_url_scheme') ?? URLSubstitution::backwards([
				'page' => $call->get_option('page'),
				'amount' => $call->get_option('amount')
			], $request);
		} else {
			$this->page = null;
			$this->amount = null;
			$this->pagination_scheme = null;
		}


		$options = $call->get_option('options');

		if(!is_null($options) && !is_array($options)){
			throw new ControllerException(500, 'Route: Invalid option «options».');
		}

		$this->options = $options ?? [];
	}


	public function execute(Request &$request) : void {
		if($this->action === 'empty'){
			$this->status_code = 200;
			return; // TODO status code
		} else if($this->action === 'list'){
			if(is_null($this->amount) || $this->page === 1){
				$offset = null;
			} else {
				$offset = $this->amount * ($this->page - 1);
			}

			try {
				$this->entities->pull($this->amount, $offset, $this->options);
				$this->status_code = 200;
			} catch(EmptyResultException $e){
				$this->status_code = 200;
			}

			$this->entities->count_total();

		} else if($this->action === 'new'){
			try {
				$this->entity->create();
				$this->entity->receive_input($request->get_post_data()); // TEMP
				$this->entity->push();
				$this->status_code = 201;
			} catch(AttributeValueExceptionList $e){
				$this->status_code = 422;
				throw new ControllerException(422, '', $e);
				return; // TODO invalid input
			}

		} else { # action==='show'|'edit'|'delete'
			try {
				$this->entity->pull($this->identifier, $this->identify_by);
				$this->entity->get_relationships()?->count_total(); // TEMP
			} catch(EmptyResultException $e){
				$this->status_code = 404;
				throw new ControllerException(404, 'Entity not found.');
			}

			if($this->action === 'show'){
				$this->status_code = 200;
				return;

			} else if($this->action === 'edit'){
				try {
					$this->entity->receive_input($request->get_post_data());
					$this->entity->push();
					$this->status_code = 200;
				} catch(AttributeValueExceptionList $e){
					$this->status_code = 422;
					throw new ControllerException(422, '', $e);
					return; // TODO invalid input
				}

			} else if($this->action === 'delete'){
				// TODO require the id to avoid accidental deletions

				$this->entity->delete();

				$this->status_code = 200;
			}
		}
	}


	public function finish() : void {
		$this->entity?->freeze();
		$this->entities?->freeze();

		if(isset($this->entities)){
			$pagination_count = $this->entities->count_total();
		} else if($this->entity?->get_relationships() !== null){
			$pagination_count = $this->entity->get_relationships()->count_total();
		} else {
			$pagination_count = null;
		}

		if($pagination_count !== null){
			$this->pagination = new Pagination($this->page, $this->amount, $pagination_count, $this->pagination_scheme);
		} else {
			$this->pagination = null;
		}

	}


	public function get_action() : string {
		return $this->action;
	}
}
?>
