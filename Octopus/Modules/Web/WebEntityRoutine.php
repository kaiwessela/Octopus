<?php
namespace Octopus\Modules\Web;
use Exception;
use Octopus\Core\Controller\Router\URLSubstitution;
use Octopus\Core\Controller\StandardEntityRoutine;
use Octopus\Core\Controller\StandardRoutine;
use Octopus\Modules\Web\WebEnvironment;
use Octopus\Modules\Web\WebRoutine;

class WebEntityRoutine extends StandardWebRoutine implements WebRoutine {
	protected array $options;


	public function load(array $options) : void {
		$this->options = $options;
	}


	public function run() : void {
		if(!$this->environment->get_request()->method_is(['GET', 'POST'])){
			throw new Exception();
		}

		$action = $this->options['action'];
		
		if($action === 'edit' || $action === 'delete' && $this->environment->get_request()->method_is('GET')){
			$action = 'pull';
		}

		if($action === 'new' && $this->environment->get_request()->method_is('GET')){
			$action = 'none';
		}

		$standard_routine = new StandardEntityRoutine();
		$standard_routine->load(
			action: $this->options['action'],
			class: $this->options['entity'],
			identifier: URLSubstitution::replace($this->options['identifier'] ?? null, $this->environment->get_request()),
			identify_by: URLSubstitution::replace($this->options['identify_by'] ?? null, $this->environment->get_request()),
			include_attributes: $this->options['include_attributes'] ?? [],
			order_by: $this->options['order_by'] ?? [],
			limit: $this->options['limit'] ?? null,
			offset: $this->options['offset'] ?? null,
			conditions: $this->options['conditions'] ?? []
		);

		$this->environment->substitute($standard_routine, $this->name);
	}

}