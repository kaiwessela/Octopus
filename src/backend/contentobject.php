<?php
namespace Blog\Backend;

// TODO to trait
class ContentObject {
	public $id; 		# String(8)[base16]
	public $longid; 	# String(9-128)[a-z0-9-]


	protected function import_check_id_and_longid($id, $longid) {
		if($id != $this->id || $longid != $this->longid){
			throw new InvalidInputException('id/longid', 'original id and longid', $data['id'] . ' ' . $data['longid']);
		}
	}

	protected function import_longid($longid) {
		if(!isset($longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}');
		}

		if(!preg_match('/^[a-z0-9-]{9,128}$/', $longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}', $longid);
		}

		try {
			$test = $this->pull_by_longid($longid);
		} catch(Exception $e){
			if($e instanceof DatabaseException){
				throw $e;
			}

			$not_found = true;
		}

		if($not_found){
			$this->longid = $longid;
		} else {
			throw new InvalidInputException('longid', ';already-exists', $longid); // TODO to special exception
		}
	}
}
?>
