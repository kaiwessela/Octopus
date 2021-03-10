<?php
# SQL UPDATE
$sql = <<<SQL

START TRANSACTION;

ALTER TABLE images
	RENAME TO media,
	RENAME COLUMN image_id TO medium_id,
	RENAME COLUMN image_longid TO medium_longid,
	CHANGE COLUMN image_extension medium_extension VARCHAR(10) NOT NULL,
	CHANGE COLUMN image_description medium_description VARCHAR(250) NULL,
	CHANGE COLUMN image_copyright medium_copyright VARCHAR(250) NULL,
	DROP COLUMN image_sizes,
	ADD medium_class SET('application', 'audio', 'image', 'video') NOT NULL AFTER medium_longid,
	ADD medium_type VARCHAR(80) NOT NULL AFTER medium_class,
	ADD medium_title VARCHAR(60) NULL AFTER medium_extension,
	ADD medium_alternative VARCHAR(250) NULL AFTER medium_copyright,
	ADD medium_variants JSON NOT NULL AFTER medium_alternative;

ALTER TABLE imagefiles
	RENAME TO mediafiles,
	CHANGE COLUMN imagefile_id mediafile_medium_id,
	CHANGE COLUMN imagefile_data mediafile_data,
	ADD mediafile_variant VARCHAR(60) NULL,
	ADD mediafile_type VARCHAR(60) NOT NULL,
	ADD mediafile_extension VARCHAR(60) NOT NULL,
	DROP INDEX imagefile_id,
	ADD UNIQUE (mediafile_medium_id, mediafile_variant);

COMMIT;



SQL;
?>
