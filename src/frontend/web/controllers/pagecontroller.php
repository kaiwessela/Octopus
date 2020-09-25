<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Frontend\Web\Controllers\Exceptions\InvalidParameterException;

class PageController extends Controller {
	const MODEL = '';

	public $errors = [
		'404' => false
	];

	/* @inherited
	const MODEL;

	protected $params;
	protected $models;

	public $objects;
	public $errors;
	*/


	public function prepare($parameters) {
		if(preg_match('/^\?([0-9])?/', $parameters['name'], $matches)){
			$this->params->name = $_GET[$matches[1]];
		} else if(is_string($parameters['name'])){
			$this->params->name = $parameters['name'];
		} else {
			throw new InvalidParameterException('name', 'string|QueryParam', $parameters);
		}

		if(is_string($parameters['title'])){
			$this->params->title = $params['title'];
		} else {
			$this->params->title = 'Seite';
		}
	}

	public function execute() {
		if(file_exists(__DIR__ . '/../pages/' . $this->params->name . '.html')){
			$this->models[0]['content'] = file_get_contents(__DIR__ . '/../pages/' . $this->params->name . '.html');
			$this->models[0]['title'] = $this->params->title;
		} else {
			$this->models = [];
			$this->errors['404'] = true;
		}
	}

	public function process() {
		$this->objects[0] = (object) [];
		$this->objects[0]->content = $this->models[0]['content'];
		$this->objects[0]->title = $this->models[0]['title'];
	}
}
?>
