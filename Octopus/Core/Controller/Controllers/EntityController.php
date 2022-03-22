<?php
namespace Octopus\Core\Controller\Controllers;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Exceptions\ControllerException;
use \Octopus\Core\Controller\Router\ControllerCall;
use \Octopus\Core\Controller\Router\URLSubstitution;

class EntityController extends Controller {
	protected string $mode; # 'REST'|'FORM' (default)
	protected string $action; # 'show'|'edit'|'delete'|'list'|'new'

	# for action==='show'|'edit'|'delete'
	protected ?string $identify_by;
	protected mixed $identifier;

	# for action==='list'
	protected ?int $amount;
	protected ?int $page;

	protected array $options;


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
			if($this->mode === 'REST' && !$this->request->is_get()){
				throw new ControllerException(405, 'Method not allowed.');
			}

			$list_class = $entity_class::LIST_CLASS;

			$this->entity = null;
			$this->entities = new $list_class();
		} else {
			if($action === 'new'){
				if($this->mode === 'REST' && !$this->request->method_is('POST')){
					throw new ControllerException(405, 'Method not allowed.');
				}
			} else if($action === 'show'){
				if($this->mode === 'REST' && !$this->request->method_is('GET')){
					throw new ControllerException(405, 'Method not allowed.');
				}
			} else if($action === 'edit'){
				if($this->mode === 'REST' && !$this->request->method_is('PUT')){
					throw new ControllerException(405, 'Method not allowed.');
				}
			} else if($action === 'delete'){
				if($this->mode === 'REST' && !$this->request->method_is('DELETE')){
					throw new ControllerException(405, 'Method not allowed.');
				}
			} else {
				throw new ControllerException(500, 'Route: Invalid option «action».');
			}

			$this->entity = new $entity_class();
			$this->entities = null;
		}

		$this->action = $action;

		if($this->action === 'show' || $this->action === 'edit' || $this->action === 'delete'){
			if($call->has_option('identify_by')){
				if(!is_string($call->get_option('identify_by'))){
					throw new ControllerException(500, 'Route: Invalid option «identify_by».');
				}

				$this->identify_by = $call->get_option('identify_by');
			} else {
				$this->identify_by = 'id';
			}

			$this->identifier = URLSubstitution::replace($call->get_option('identifier'), $request); // TODO validate this
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
		}

		$options = $call->get_option('options');

		if(!is_null($options) && !is_array($options)){
			throw new ControllerException(500, 'Route: Invalid option «options».');
		}

		$this->options = $options ?? [];
	}


	public function execute() : void {
		if($this->action === 'list'){
			if(is_null($this->amount) || $this->page === 1){
				$offset = null;
			} else {
				$offset = $this->amount * ($this->page - 1);
			}

			try {
				$this->entities->pull($this->amount, $offset, $this->options);
				$this->entities->count_total();
				$this->status = 200;
			} catch(EmptyResultException $e){
				$this->status = 200;
				return; // no entities found, 200 but empty result
			}

		} else if($this->action === 'new'){
			if(!$this->request->method_is('POST')){
				$this->entity = null; // blank page because no data was submitted
			}

			try {
				$this->entity->receive_input($this->request->post);
				$this->entity->push();
				$this->status = 201;
			} catch(AttributeValueExceptionList $e){
				return; // TODO invalid input
			}

		} else { # action==='show'|'edit'|'delete'
			try {
				$this->entity->pull($this->identifier, $this->identify_by);
			} catch(EmptyResultException $e){
				throw new ControllerException(404, 'Entity not found.');
			}

			if($this->action === 'show' || ($this->mode === 'FORM' && $this->request->method_is('GET'))){
				$this->status = 200;
				return;

			} else if($this->action === 'edit'){
				try {
					$this->entity->receive_input($this->request->post);
					$this->entity->push();
				} catch(AttributeValueExceptionList $e){
					return; // TODO invalid input
				}

			} else if($this->action === 'delete'){
				// TODO require the id to avoid accidental deletions

				$this->entity->delete();

				$this->status = 200;
			}
		}
	}


	public function finish() : void {

	}
}
?>
