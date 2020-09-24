<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\ControllerModules;

trait ControllerModules {
	protected function prepare_mode($parameters) {
		if($parameters['mode'] == 'single' || $parameters['mode'] == 'multi' || $parameters['mode'] == 'new'){
			$this->params->mode = $parameters['mode'];
		} else {
			throw new InvalidParameterException('mode', 'single|multi|new', $parameters);
		}
	}

	protected function prepare_action($parameters) {
		$valid_actions = ['list', 'show', 'new', 'edit', 'delete'];
		if(in_array($parameters['action'] ?? '', $valid_actions)){
			$this->action = new ControllerAction($parameters['action']);
		}
	}

	protected function prepare_amount($parameters) {
		if(is_numeric($parameters['amount']) && $parameters['amount'] > 0){
			$this->params->amount = (int) $parameters['amount'];
		} else {
			$this->params->amount = null;
		}
	}

	protected function prepare_page($parameters) {
		if(!isset($parameters['page']) || $parameters['page'] == null){
			$this->params->page = null;
		} else if(is_numeric($parameters['page']) && $parameters['page'] > 0){
			$this->params->page = $parameters['page'];
		} else if(preg_match('/^\?([0-9])?/', $parameters['page'], $matches)){
			$this->params->page = (empty($_GET[$matches[1]])) ? 1 : (int) $_GET[$matches[1]];
		} else {
			throw new InvalidParameterException('page', 'int|QueryParam', $parameters);
		}
	}

	protected function prepare_identifier($parameters) {
		if(preg_match('/^\?([0-9])?/', $parameters['identifier'], $matches)){
			$this->params->identifier = $_GET[$matches[1]];
		} else if(is_string($parameters['identifier'])){
			$this->params->identifier = $parameters['identifier'];
		} else {
			throw new InvalidParameterException('identifier', 'string|QueryParam', $parameters);
		}
	}
}
?>
