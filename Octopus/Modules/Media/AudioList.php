<?php
namespace Blog\Model\DataObjects\Lists\Media;
use \Blog\Model\DataObjects\Lists\MediaList;
use \Blog\Model\DataObjects\Media\Audio;

class AudioList extends MediaList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Audio::class;
	const OBJECTS_ALIAS = 'audio';


	const SELECT_QUERY = <<<SQL
SELECT * FROM media WHERE medium_class = 'audio'
ORDER BY medium_longid
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM media WHERE medium_class = 'audio'
SQL; #---|

}
?>
