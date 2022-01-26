<?php # Modules/Media/Application.php 2021-10-31
namespace Octopus\Modules\Media;
use \Octopus\Modules\Media\Medium;
use \Octopus\Core\Model\FileHandler\ApplicationFile;

class Application extends Medium {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	# inherited from Medium:
	# protected ?string $name;
	# protected ?string $copyright;
	# protected string 	$type;
	# protected string 	$extension;
	# protected ?string $description;
	# protected ?string $alternative;
	# protected ?array 	$variants;

	# protected ?File $file;
	# protected ?array $variant_files;


	const FILE_CLASS = ApplicationFile::class;
	const DB_CLASS_STRING = 'application';


	protected function autoversion() : void {}



	const QUERY_PULL_BY_ID = self::QUERY_PULL_START . <<<SQL
WHERE medium_id = :id AND medium_class = 'application'
SQL;

	const QUERY_PULL_BY_LONGID = self::QUERY_PULL_START . <<<SQL
WHERE medium_longid = :id AND medium_class = 'application'
SQL;

	const QUERY_PULL_BY_ID_OR_LONGID = self::QUERY_PULL_START . <<<SQL
WHERE (medium_id = :id OR medium_longid = :id) AND medium_class = 'application'
SQL;

}
?>
