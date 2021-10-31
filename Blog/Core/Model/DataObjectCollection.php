<?php // CODE --, COMMENTS --, IMPORTS --
namespace Blog\Core\Model;

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


$objectlists = [
	*type => DataObjectList
]

*/


	function __construct(?array $idlists) {
		$this->idlists = $idlists;
	}


	public function pull() : void {
		// $this->require_empty();

		foreach($this->idlists as $type => $ids){
			$objectlist = $this::get_type_object_list($type);
			$objectlist->pull_by_ids($ids);
			$this->objectlists[$type] = $objectlist;
		}
	}


	public function import(?array $data) : void {

	}


	public function db_export() : string {

	}


	public function get_type(string|DataObject $id_or_object) : ?string {
		if($id_or_object instanceof DataObject){
			$id = $id_or_object->id;
		} else {
			$id = $id_or_object;
		}

		$result = null;
		foreach($this->idlists as $type => $ids){
			if(in_array($id, $ids)){
				$result = $type;
			}
		}

		return $result;
	}


	public function get_object(string $id, ?string $type = null) : ?DataObject {
		$type = $type ?? $this->get_type($id);
		if($type == null){
			return null;
		}

		return $this->objectlists[$type]->get($id);
	}


	private static function get_type_object_list(string $type) : DataObjectList {
		$object_class = ControllerConfig::REGISTERED_DATA_OBJECTS[$type] ?? null;
		if(empty($object_class)){
			throw new Exception('object class not found');
		}

		if(!in_array($object_class, self::SUPPORTED_OBJECT_CLASSES)){
			throw new Exception('unsupported object class');
		}

		$list_class = ControllerConfig::DATA_OBJECT_LISTS[$object_class] ?? null;
		if(empty($list_class)){
			throw new Exception('list class not found');
		}

		return new $list_class();
	}


	const SUPPORTED_OBJECT_CLASSES = [
		Image::class,
		Event::class,
		Motion::class,
		Person::class
	];


}
?>
