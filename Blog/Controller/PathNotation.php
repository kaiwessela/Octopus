<?php
namespace Blog\Controller;
use Exception;

class PathNotation {
	private string $notation;
	private string $type;
	private string $regex;


	function __construct(string $notation) {
		$this->notation = $notation;

		$regex = '/^\/\^.*\$\/$/';
		$pathpattern = '/^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$/';

		if($this->notation == '/'){
			$this->type = 'empty';
		} else if($this->notation == '?'){
			$this->type = 'any';
		} else if(preg_match($regex, $this->notation)){
			$this->type = 'regex';
		} else if(preg_match($pathpattern, $this->notation)){
			$this->type = 'pathpattern';
		} else {
			throw new Exception("PathNotation » notation invalid: '$notation'.");
		}
	}


	function __toString() {
		return $this->resolve();
	}


	public function resolve() {
		if(empty($this->regex)){
			if($this->type == 'empty'){
				$this->regex = '/^$/';
			} else if($this->type == 'any'){
				$this->regex = '/^.*$/';
			} else if($this->type == 'regex'){
				$this->regex = $this->notation;
			} else if($this->type == 'pathpattern'){
				$this->regex = '/^' . str_replace(
					['*{',    '#{',     '/*?',       '/#?',        '*',     '#',      '/'	],
					['[^/]{', '[0-9]{', '(/[^/]+)?', '(/[0-9]+)?', '[^/]+', '[0-9]+', '\/'	],
					$this->notation
				) . '$/';
			} else {
				throw new Exeption('PathNotation » invalid type. this should never happen.');
			}
		}

		return $this->regex;
	}


	public function match($path) {

	}
}
?>
