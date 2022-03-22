<?php
namespace Octopus\Core\Controller\Router;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Exceptions\ControllerException;

class TargetDefinition {
	private string $method;
	private string $path;


	function __construct(string $subject) {
		list($method, $path) = explode(' ', $subject, 2);

		if($method !== '*' && !in_array($method, self::HTTP_METHODS)){
			throw new ControllerException(500, "Invalid method «{$method}» on route «{$subject}».");
		}

		if(!is_string($path) || $path === ''){
			throw new ControllerException(500, "Invalid path «{$path}» on route «{$subject}» (no string or empty).");
		}

		$this->method = $method;
		$this->path = $path;
	}


	public function match_method(Request $request) : bool {
		if($this->method === '*'){
			return true;
		} else {
			return $this->method === $request->get_method();
		}
	}


	public function match_path(Request $request) : bool {
		if(str_starts_with($this->path, '/')){
			try {
				return self::match_placeholders(ltrim($this->path, '/'), ltrim($request->get_virtual_path(), '/'));
			} catch(ControllerException $e){
				throw new ControllerException(500, "Invalid path «{$path}» on route «{$subject}» {$e->getMessage()}.");
			}
		} else if(preg_match('/^\^.*\$$/', $this->path)){
			$regex_test = preg_match($this->path, $request->get_virtual_path());

			if($regex_test === 1){
				return true;
			} else if($regex_test === 0){
				return false;
			} else { # $regex_test === false
				throw new ControllerException(500, "Invalid path «{$path}» on route «{$subject}» (invalid regex).");
			}
		} else {
			throw new ControllerException(500, "Invalid path «{$path}» on route «{$subject}» (unknown pattern type).");
		}
	}


	private static function match_placeholders(string $pattern, string $subject) : bool {
		if($pattern === '' && $subject === ''){
			return true;
		} else if($pattern === ''){
			return false;
		}

		@list($p, $pr) = explode('/', $pattern, 2);
		@list($s, $sr) = explode('/', $subject, 2);

		if($p === '#'){
			if(!is_numeric($s) || ceil($s) !== floor($s)){
				return false;
			}
		} else if($p === '#?'){
			if(!empty($pr)){
				throw new ControllerException(500, '(optional segment before end)');
			}

			if(!empty($sr)){
				return false;
			}

			if(empty($s) || (is_numeric($s) && ceil($s) === floor($s))){
				return true;
			}
		} else if($p === '**'){
			if(!empty($pr)){
				throw new ControllerException(500, '(catch-all segment before end)');
			}

			return true;
		} else if($s !== $p && $p !== '*'){
			return false;
		}

		return self::match_placeholders($pr ?? '', $sr ?? '');
	}

	const HTTP_METHODS = ['GET', 'POST', 'HEAD', 'PUT', 'PATCH', 'DELETE', 'TRACE', 'OPTIONS'];
}
?>