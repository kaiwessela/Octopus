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

	public string $identifier;

	public int $amount;
	public int $page;



	function __construct(string $name, array $settings, string $mode, Request &$request) {
		if($mode == 'controller'){
			if(empty(ControllerConfig::REGISTERED_CONTROLLERS[$name])){
				throw new Exception("Call » controller not found: '$name'.");
			}

			$this->controller = ControllerConfig::REGISTERED_CONTROLLERS[$name];
			$this->settings = $settings;
			$this->dataobject = null;

		} else if($mode == 'object'){
			$dosubs = new Substitution($name, $request);
			$doname = $dosubs->resolve();

			if(empty(ControllerConfig::REGISTERED_DATA_OBJECTS[$doname])){
				throw new Exception("Call » data object not found: '$doname'.");
			}


			$this->varname = $settings['as'] ?? $doname;
			if(!is_string($this->varname)){
				throw new Exception("Call » invalid 'as' value or object name on '$name'.");
			}
			// TODO check if varname is set where it should be set as a global variable

			$this->options = $settings['options'] ?? [];


			$this->action = $settings['action'];
			if($this->action == 'list'){ // TODO count action

				$dataobject = ControllerConfig::REGISTERED_DATA_OBJECTS[$doname];
				$this->dataobject = ControllerConfig::DATA_OBJECT_LISTS[$dataobject];
				$this->controller = ControllerConfig::DATA_OBJECT_CONTROLLERS[$dataobject];

				if(!is_subclass_of($this->dataobject, DataObjectList::class)){
					throw new Exception('Call » invalid dataobject.');
				}

				$amount = new Substitution($settings['amount'], $request, 10);
				$this->amount = (int) $amount->resolve();
				if(empty($this->amount) || !is_int($this->amount)){
					throw new Exception("Call » invalid amount on '$name'.");
				}

				$page = new Substitution($settings['page'], $request, 1);
				$this->page = (int) $page->resolve();
				if(empty($this->page) || !is_int($this->page)){
					throw new Exception("Call » invalid page on '$name'.");
				}

			} else if(in_array($this->action, ['new', 'show', 'edit', 'delete'])){

				$this->dataobject = ControllerConfig::REGISTERED_DATA_OBJECTS[$doname];
				$this->controller = ControllerConfig::DATA_OBJECT_CONTROLLERS[$this->dataobject];

				if(!is_subclass_of($this->dataobject, DataObject::class)){
					throw new Exception('Call » invalid dataobject.');
				}

				$identifier = new Substitution($settings['identifier'], $request);
				$this->identifier = $identifier->resolve();
				if(empty($this->identifier) || !is_string($this->identifier)){
					throw new Exception("Call » invalid identifier on '$name'.");
				}

				if($this->dataobject::PAGINATABLE){
					$amount = new Substitution($settings['amount'], $request, 10);
					$this->amount = (int) $amount->resolve();
					if(empty($this->amount) || !is_int($this->amount)){
						throw new Exception("Call » invalid amount on '$name'.");
					}

					$page = new Substitution($settings['page'], $request, 1);
					$this->page = (int) $page->resolve();
					if(empty($this->page) || !is_int($this->page)){
						throw new Exception("Call » invalid page on '$name'.");
					}
				}

			} else {
				throw new Exception("Call » invalid action '$this->action' on '$name'.");
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
