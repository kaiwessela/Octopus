<?php
namespace Blog\Frontend\Web\Modules;
use Blog\Frontend\Web\Router\Exceptions\InvalidNotationException;
use Blog\Frontend\Web\Router\Exceptions\InvalidRouteAttributeException;
use Blog\Frontend\Web\Router\Exceptions\InvalidRoutingTableException;
use Blog\Frontend\Web\Router\Exceptions\RouteNotFoundException;
use Blog\Frontend\Web\ControllerRequest;

class Router {
	public $path;
	public $template;
	public $auth;
	public $controller_requests = [];

	private $routes;
	private $route;


	function __construct(string $routes_json) {
		# load the requested path (https://example.org/test/path)
		#                               this section: ~~~~~~~~~~
		$this->path = trim($_SERVER['REQUEST_URI'], '/'); # remove leading and ending slashes

		# parse the routing json to an array, throw error if it fails
		$this->routes = json_decode($routes_json, true, \JSON_THROW_ON_ERROR);

		# check if routing table is an array
		if(!is_array($this->routes)){
			throw new InvalidRoutingTableException($this->routes);
		}

		# try to find the route corresponding to the requested path
		# @param $path: route path as plain text, wildcard, pathpattern or regex;
		# @param $settings: settings specified for the route in the routing file;
		foreach($this->routes as $path => $settings){
			# rewrite plain text, wildcard or pathpattern to regex
			$regex = $this->rewrite_to_regex($path);

			# check if path regex corresponds to requested path
			if(preg_match($regex, $this->path)){
				# it does; set this route and break the loop
				$this->route = $settings;
				break;
			}
		}

		# check if a route was found at all
		if(!$this->route){
			throw new NoRouteFoundException();
		}

		$this->set_template($this->route['template']);
		$this->set_auth($this->route['auth']);

		foreach($this->route['controllers'] as $class => $settings){
			$class = $this->resolve_substitutions($class);

			$this->controller_requests[] = new ControllerRequest($this, $class, $settings);
		}
	}

	private function set_template($raw_template) {
		# check if raw template is a string
		if(!is_string($raw_template)){
			throw new InvalidRouteAttributeException();
		} else {
			$template = $this->resolve_substitutions($raw_template);

			# check if template contains any forbidden directory segments, such as /../
			# template files may only sit in template dir or a child of that
			if(preg_match('/(\.\.\/|\/\.\.)/', $template)){
				throw new InvalidRouteAttributeException();
			} else {
				# there was no problem, set template as router template attribute
				$this->template = $template;
			}
		}
	}

	private function set_auth($raw_auth = null) {
		# check if auth attribute is set
		if(is_null($raw_auth)){
			$this->auth = false; # set default auth value as router auth attribute
		} else if(is_bool($raw_auth)){
			$this->auth = $raw_auth; # auth attribute is set and a boolean, set it
		} else {
			throw new InvalidRouteAttributeException();
		}
	}

	public function resolve_substitutions($string) {
		$segments = explode('/', $this->path); # create a segment list from path

		# match the string, ?n => $matches[1] = n
		return preg_replace_callback('/\?([0-9]+)/', function($matches) use ($segments){
			# -1 because arrays count from 0 but path segments from 1
			return $segments[$matches[1] - 1] ?? null;
		}, $string);
	}

	private function rewrite_to_regex($notation) {
		# pathpattern definition
		$pathpattern = '/^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$/';
		# regex definition
		$regex = '/^\/\^.*\$\/$/';

		if($notation == '/'){ # test for 'empty' wildcard
			return '/^$/'; # empty matching regex
		} else if($notation == '*'){ # test for 'any' wildcard
			return '/^.*$/'; # any character matching regex
		} else if(preg_match($regex, $notation)){ # test for regex
			return $notation; # regex stays the same
		} else if(preg_match($pathpattern, $notation)){ # test for pathpattern
			return '/^' . str_replace( # replace characters as described below and add slashes
				['*{',	  '#{',     '/*?',       '/#?',        '*',     '#',      '/'  ],
				['[^/]{', '[0-9]{', '(/[^/]+)?', '(/[0-9]+)?', '[^/]+', '[0-9]+', '\/' ],
				$notation
			) . '$/';
		} else {
			throw new InvalidNotationException();
		}
	}
}

/* ================================
ROUTE SYNTAX:
{
	'regex | pathpattern' [<-path]: {
		'template': 'string | string with placeholders',
		'controllers': {
			'string | placeholder' [<- controller name]: {
				'action': 'string',

				#for action=show|edit|delete
				'identifier': 'string | placeholder'

				#for action=list
				'amount': int(>0) [default: 5]
				'page': int(>0) | 'placeholder' [default: 1]

				[… controller-specific values]
			},
			[… more controllers]
		},
		'auth': bool [default: false]
	}
}


REGEX SYNTAX:
/^\/\^.*\$\/$/

PATHPATTERN SYNTAX:
/^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$/



PATHPATTERN REWRITING:
*{		=>	[^/]{				recursion 1
#{		=>	[0-9]{
/*?		=>	(/[^/]+)?			recursion 2
/#?		=>	(/[0-9]+)?
*		=>	[^/]+				recursion 3
#		=>	[0-9]+
/		=>	\/					recursion 4

-- add /^ and $/


PATH WILDCARDS:
"/"		=>	/^$/		(empty wildcard)
"*"		=>	/^.*$/		(any wildcard)


PLACEHOLDER SYNTAX:
/^\?([0-9])

   ================================ */
?>
