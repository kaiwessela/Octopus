<?php
namespace Octopus\Modules\Web\Routing;
use Octopus\Core\Controller\Exceptions\ControllerException;

class Route {
	protected string $target;
	protected ?string $target_method;
	protected ?string $target_path;
	protected array $options;


	function __construct(string $target, array $options) {
		$this->target = $target;

		$this->options = $options;

		if(str_starts_with($target, '@')){
			$this->target_method = null;
			$this->target_path = null;
		} else {
			$segments = explode(' ', $target, 2);

			if(count($segments) !== 2){
				throw new ControllerException(500, "Invalid target on route «{$target}».");
			}

			$this->target_method = $segments[0];
			$this->target_path = $segments[1];

			// TODO check formal validity? -> debug mode
		}
	}


	public function match_method(string $method) : bool {
		if(!isset($this->target_method)){
			return false;
		} else if($this->target_method === '*'){
			return true;
		} else {
			return str_contains($this->target_method, $method);
		}
	}


	public function match_path(string $path) : bool {
		if(!isset($this->target_path)){
			return false;
		} else if($this->target_path === '.'){
			return $path === '';
		} else if(str_starts_with($this->target_path, '/')){
			return self::match_path_regex($this->target, $this->target_path, $path);
		} else {
			return self::match_path_pattern($this->target, $this->target_path, $path);
		}
	}


	protected static function match_path_regex(string $target, string $regex, string $request_path) : bool {
		$result = preg_match($regex, $request_path);

		if($result === false){
			throw new ControllerException(500, "Invalid target path on route «{$target}»: malformed regex.");
		} else {
			return (bool) $result;
		}
	}


	protected static function match_path_pattern(string $target, string $pattern, string $request_path) : bool {
		if($pattern === ''){
			return ($request_path === '');
		}
		
		$ppr = explode('/', $pattern, 2);
		$p = $ppr[0];
		$pr = $ppr[1] ?? '';

		$ssr = explode('/', $request_path, 2);
		$s = $ssr[0];
		$sr = $ssr[1] ?? '';

		if($p === '#'){
			if(!is_numeric($s) || ceil($s) !== floor($s)){
				return false;
			}
		} else if($p === '#?'){
			if(!empty($pr)){
				throw new ControllerException(500, "Invalid target path on route «{$target}»: optional segment before end.");
			}

			if(empty($sr)){
				return (empty($s) || (is_numeric($s) && ceil($s) === floor($s)));
			} else {
				return false;
			}
		} else if($p === '**'){
			if(!empty($pr)){
				throw new ControllerException(500, "Invalid target path on route «{$target}»: catch-all segment before end.");
			}

			return true;
		} else if($s !== $p && $p !== '*'){
			return false;
		}

		return self::match_path_pattern($target, $pr ?? '', $sr ?? '');
	}


	public function get_options() : array {
		return $this->options;
	}
}