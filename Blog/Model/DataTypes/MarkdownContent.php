<?php
namespace Blog\Model\DataTypes;
use \Blog\Model\Abstracts\DataType;
use \Parsedown\Parsedown;
use \Blog\Model\Abstracts\DataObjectCollection;

class MarkdownContent implements DataType {
	private string $raw;
	private ?string $parsed;


	function __construct(string $value) {
		$this->raw = $value;
	}

	function __toString() {
		return $this->raw;
	}

	public static function import(string $value) : MarkdownContent {
		return new MarkdownContent($value);
	}

	public function parse() : string {
		if(empty($this->parsed)){
			$this->parsed = Parsedown::instance()->text($this->raw);
		}

		return $this->parsed;
	}

	public function echo(callable $resolver, DataObjectCollection $collection) : void {
		$parsed = $this->parse();

		$lines = explode(PHP_EOL, $parsed);
		foreach($lines as $line){
			if(str_starts_with($line, '<p>[[') && preg_match('/^<p>\[\[([0-9a-f]{8})\]\]<\/p>$/', $line, $matches)){
				$id = $matches[1];
				$type = $collection->get_type($id);
				$object = $collection->get_object($id, $type);

				$resolver($object, $type);

			} else {
				echo $line;
			}
		}


		/*
		function resolver(DataObject $object, string $type){

		}
		*/
	}

	public function staticize() {
		return $this->parse();
	}
}
?>
