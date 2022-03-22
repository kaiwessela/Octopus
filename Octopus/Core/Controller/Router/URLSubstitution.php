<?php
namespace Octopus\Core\Controller\Router;
use \Octopus\Core\Controller\Request;

class URLSubstitution {

	public static function replace(string|array $subject, Request $request) : mixed {
		if(is_array($subject)){
			foreach($subject as $key => $value){
				if(is_string($value) || is_array($value)){
					$subject[$key] = static::replace($value, $request);
				}
			}

			return $subject;
		} else if(preg_match('/^(\/([0-9]+)(\+)?|\?([A-Za-z0-9-_.]+))(\|([A-Za-z0-9-_.]+))?$/', $subject, $matches)){
			# matches:			[1 [2     ][3  ]   [4              ]][5 [6              ]]
			#
			# examples/matches:	1		2		3		4		5		6
			# /3				/3		3		-		-		-		-
			# /3|bar			/3		3		-		-		|bar	bar
			# /4+				/4+		4		+		-		-		-
			# /4+|baz			/4+		4		+		-		|baz	baz
			# ?test				?test	-		-		test	-		-
			# ?test|foo			?test	-		-		test	|foo	foo

			if(!empty($matches[2])){
				if(!empty($matches[3])){
					$replacement = $request->get_virtual_path_from_segment($matches[2]);
				} else {
					$replacement = $request->get_virtual_path_segment($matches[2] - 1); // TODO check this
				}
			} else if(!empty($matches[4])){
				$replacement = $request->get_query_value($matches[4]);
			}

			if(empty($replacement)){
				$replacement = empty($matches[6]) ? null : $matches[6];
			}

			if($replacement === 'true' || $replacement === 'false'){
				return ($replacement === 'true'); // converts to bool
			} else if(is_numeric($replacement)){
				if(floor($replacement) === ceil($replacement)){
					return (int) $replacement;
				} else {
					return (float) $replacement;
				}
			} else {
				return $replacement;
			}
		} else {
			return preg_replace_callback('/{(\/[0-9]+\+?|\?[A-Za-z0-9-_.]+)(\|[A-Za-z0-9-_.]+)?}/', function($matches){
				return (string) static::replace($matches[1], $request);
			}, $subject);
		}
	}
}
?>
