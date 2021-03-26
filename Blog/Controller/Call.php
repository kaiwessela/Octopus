<?php
namespace Blog\Controller;
use \Blog\Config\ControllerConfig;
use \Blog\Controller\Controller;
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



	function __construct(string $name, array $settings, string $mode) {
		if($mode == 'controller'){
			if(empty(ControllerConfig::REGISTERED_CONTROLLERS[$name])){
				throw new Exception("Call » controller not found: '$name'.");
			}

			$this->controller = ControllerConfig::REGISTERED_CONTROLLERS[$name];
			$this->settings = $settings;
			$this->dataobject = null;

		} else if($mode == 'object'){
			if(empty(ControllerConfig::REGISTERED_DATA_OBJECTS[$name])){
				throw new Exception("Call » data object not found: '$name'.");
			}

			$this->dataobject = ControllerConfig::REGISTERED_DATA_OBJECTS[$name];

			if(empty(ControllerConfig::DATA_OBJECT_CONTROLLERS[$this->dataobject])){
				throw new Exception('Call » no matching controller found on '$name'.');
			}

			$this->controller = ControllerConfig::DATA_OBJECT_CONTROLLERS[$this->dataobject];

			$this->varname = $settings['as'] ?? $name;
			if(!is_string($this->varname)){
				throw new Exception("Call » invalid 'as' value or object name on '$name'.");
			}
			// TODO check if varname is set where it should be set as a global variable

			$this->options = $settings['options'] ?? null;

			if(is_subclass_of($this->dataobject, DataObject::class){
				$this->action = $settings['action'] ?? 'show'; // TODO substitution
				if(!in_array($this->action, ['new', 'show', 'edit', 'delete'])){
					throw new Exception("Call » invalid action on '$name'.");
				}

				$this->identifier = (string) new Substitution($settings['identifier']);
				if(empty($this->identifier) || !is_string($this->identifier)){
					throw new Exception("Call » invalid identifier on '$name'.");
				}

				if($this->dataobject::CONTAINER){
					$this->amount = $settings['amount'];
					if(empty($this->amount) || !is_int($this->amount)){
						throw new Exception("Call » invalid amount on '$name'.");
					}

					$this->page = (int) new Substitution($settings['page'] ?? 1);
					if(empty($this->page) || !is_int($this->page)){
						throw new Exception("Call » invalid page on '$name'.");
					}
				}

			} else if(is_subclass_of($this->dataobject, DataObjectList::class)){
				$this->action = $settings['action'] ?? 'list';
				if($this->action !== 'list'){
					throw new Exception("Call » invalid action on '$name'.");
				}

				$this->amount = $settings['amount'];
				if(empty($this->amount) || !is_int($this->amount)){
					throw new Exception("Call » invalid amount on '$name'.");
				}

				$this->page = (int) new Substitution($settings['page'] ?? 1);
				if(empty($this->page) || !is_int($this->page)){
					throw new Exception("Call » invalid page on '$name'.");
				}

			} else {
				throw new Exception('Call » invalid dataobject.');
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
