<?php
namespace Octopus\Modules\Web;
use Exception;
use Octopus\Core\Controller\Router\URLSubstitution;
use Octopus\Core\Controller\StandardEntityRoutine;
use Octopus\Core\Controller\StandardRoutine;
use Octopus\Core\Model\Attributes\IdentifierAttribute;
use Octopus\Modules\Web\WebEnvironment;

class WebEntityRoutine extends StandardWebRoutine {
	protected array $options;
	protected string $requested_action;
	protected string $executed_action;
	protected ?StandardEntityRoutine $standard_routine;

	protected ?string $redirect_on_success;


	public function load(array $options) : void {
		$this->options = $options;

		if(!isset($options['action'])){
			throw new Exception('option action missing.');
		}

		if(!in_array($options['action'], ['none', 'create', 'pull', 'list', 'edit', 'delete'])){
			throw new Exception('option action invalid.');
		}

		
	}


	public function run() : void {
		//// LOAD
		$this->requested_action = $this->options['action'];
		
		if(($this->requested_action_is('edit') || $this->requested_action_is('delete')) && $this->environment->get_request()->method_is('GET')){
			$this->executed_action = 'pull';
		} else if($this->requested_action_is('create') && $this->environment->get_request()->method_is('GET')){
			$this->executed_action = 'none';
		} else {
			$this->executed_action = $this->get_requested_action();
		}

		if($this->executed_action_is('none')){
			$this->standard_routine = null;
			return;
		}

		if(!isset($this->options['entity'])){
			throw new Exception('option entity missing.');
		}

		$this->standard_routine = new StandardEntityRoutine();


		$redirect_on_success_name = match($this->get_requested_action()){
			'create' => 'redirect-after-created',
			'edit' => 'redirect-after-edited',
			'delete' => 'redirect-after-deleted',
			default => null
		};

		if(!is_null($redirect_on_success_name) && isset($this->options[$redirect_on_success_name])){
			// CHECK HERE

			$this->redirect_on_success = $this->options[$redirect_on_success_name];
		}



		//// EXECUTE
		if($this->executed_action_is('none')){
			return;
		}

		$this->standard_routine->load(
			action: $this->executed_action,
			class: $this->options['entity'],
			identifier: URLSubstitution::replace($this->options['identifier'] ?? null, $this->environment->get_request()),
			identify_by: URLSubstitution::replace($this->options['identify_by'] ?? null, $this->environment->get_request()),
			include_attributes: $this->options['include_attributes'] ?? [],
			order_by: $this->options['order_by'] ?? [],
			limit: $this->options['limit'] ?? null,
			offset: $this->options['offset'] ?? null,
			conditions: URLSubstitution::replace($this->options['conditions'] ?? [], $this->environment->get_request())
		);

		// $this->environment->substitute($standard_routine, $this->name);
		$this->environment->run($this->standard_routine, null, true);

		if(isset($this->redirect_on_success) && $this->executed_action_is(['create', 'edit', 'delete'])){
			// CHECK
			$matches = [];
			preg_match_all('/\{([a-z0-9_-]+)\}/', $this->options[$redirect_on_success_name], $matches);

			foreach($matches[1] as $attribute_name){
				if(!$this->standard_routine->object->has_attribute($attribute_name)){
					throw new Exception('attribute undefined');
				}

				if(!$this->standard_routine->object->get_attribute($attribute_name) instanceof IdentifierAttribute){
					throw new Exception('attribute not an identifier');
				}
			}
		


			$object = $this->standard_routine->object;

			$this->environment->get_response()->set_redirect(
				preg_replace_callback(
					'/\{([a-z0-9_-]+)\}/',
					function($matches) use ($object) {
						return $this->standard_routine->object->get_attribute($matches[1])->get_value();
					},
					$this->redirect_on_success
				),
				303
			);
		}
	}


	public function get_requested_action() : string {
		return $this->requested_action;
	}


	public function get_executed_action() : string {
		return $this->executed_action;
	}


	public function requested_action_is(string|array $action) : bool {
		return is_string($action) ? $this->get_requested_action() === $action : in_array($this->get_requested_action(), $action);
	}


	public function executed_action_is(string|array $action) : bool {
		return is_string($action) ? $this->get_executed_action() === $action : in_array($this->get_executed_action(), $action);
	}


	// HOTFIX
	function __get($name) {
		if($name === 'object'){
			return $this->standard_routine?->object;
		}
	}

}