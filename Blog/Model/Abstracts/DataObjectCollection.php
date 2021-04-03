<?php
namespace Blog\Model\Abstracts;

class DataObjectCollection {
	public ?array $objectlists;
	public ?array $idlists;


/* IDLISTS
$idlists = [
	*type => *ids,
	…
]

*type = string as in ControllerConfig
*ids = [string(id0), string(id1), … , string(idn)]

*/


	function __construct(?array $idlists) {
		$this->idlists = $idlists;
	}


	public function pull() {

	}


	public function import(?array $data) {
		
	}


}
?>
