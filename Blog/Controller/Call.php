<?php
namespace Blog\Controller;
use \Blog\Config\ControllerConfig;
use \Blog\Controller\Controller;
use \Blog\Controller\Substitution;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectList;
use Exception;

class Call {
	public $controller;
	public $dataobject;

	public array $settings;

	public string $varname;
	public string $action;
	public array $options;

	public ?string $identifier;

	public ?int $amount;
	public ?int $page;



	function __construct(string $name, array $settings, string $mode, Request &$request) {
		$cname = rtrim($name, '*'); // Group, Group*, Group** etc. becomes Group

		if($mode == 'controller'){
			if(empty(ControllerConfig::REGISTERED_CONTROLLERS[$cname])){
				throw new Exception("Call » controller not found: '$name'.");
			}

			$this->controller = ControllerConfig::REGISTERED_CONTROLLERS[$cname];
			$this->settings = $settings;
			$this->dataobject = null;

		} else if($mode == 'object'){
			$objectname = Substitution::new($cname, $request)->resolve();
			if(empty(ControllerConfig::REGISTERED_DATA_OBJECTS[$objectname])){
				throw new Exception("Call » data object not found: '$objectname'.");
			}

			$this->varname = $settings['as'] ?? $objectname;
			if(!is_string($this->varname)){
				throw new Exception("Call » invalid varname: '$this->varname'.");
			}

			$this->options = $settings['options'] ?? [];
			$this->action = $settings['action'] ?? '';

			if(isset($settings['identifier']) || $this->action == 'new'){
				if($this->action != 'new'){
					$this->identifier = Substitution::new($settings['identifier'], $request)->resolve();
					if(!is_string($this->identifier)){
						throw new Exception("Call » invalid identifier: '$this->identifier'.");
					}
				} else {
					$this->identifier = null;
				}

				$this->dataobject = ControllerConfig::REGISTERED_DATA_OBJECTS[$objectname];
				$this->controller = ControllerConfig::DATA_OBJECT_CONTROLLERS[$this->dataobject];

				if(!is_subclass_of($this->dataobject, DataObject::class)){
					throw new Exception("Call » invalid dataobject: '$this->dataobject'.");
				}

				if($this->action == 'count'){
					if(!$this->dataobject::PAGINATABLE){
						throw new Exception('Call » dataobject not countable.');
					}

					$supports_amount_and_page = false;
				} else if(in_array($this->action, ['new', 'show', 'edit', 'delete'])){
					$supports_amount_and_page = $this->dataobject::PAGINATABLE;
				} else {
					throw new Exception("Call » invalid action: '$this->action'.");
				}

			} else {
				$this->identifier = null;

				$objectclass = ControllerConfig::REGISTERED_DATA_OBJECTS[$objectname];
				$this->dataobject = ControllerConfig::DATA_OBJECT_LISTS[$objectclass];
				$this->controller = ControllerConfig::DATA_OBJECT_CONTROLLERS[$objectclass];

				if(!is_subclass_of($this->dataobject, DataObjectList::class)){
					throw new Exception("Call » invalid dataobject: '$this->dataobject'.");
				}

				if($this->action == 'list'){
					$supports_amount_and_page = true;
				} else if($this->action == 'count'){
					$supports_amount_and_page = false;
				} else {
					throw new Exception("Call » invalid action: '$this->action'.");
				}
			}

			if($supports_amount_and_page){
				$this->amount = Substitution::new($settings['amount'] ?? null, $request, numeric:true)->resolve();
				if(!is_null($this->amount) && (!is_int($this->amount) || $this->amount <= 0)){
					throw new Exception("Call » invalid amount: '$this->amount'.");
				}

				$this->page = Substitution::new($settings['page'] ?? null, $request, numeric:true)->resolve();
				if(!is_null($this->page) && (!is_int($this->page) || $this->page <= 0)){
					throw new Exception("Call » invalid page: '$this->page'.");
				}
			} else {
				$this->amount = null;
				$this->page = null;
			}

			if(empty($this->controller)){
				throw new Exception("Call » no matching controller found on '$name'.");
			}

		} else {
			throw new Exception("Call » invalid mode: '$mode'.");
		}

		if(!is_subclass_of($this->controller, Controller::class)){
			throw new Exception('Call » invalid controller.');
		}
	}
}
?>
