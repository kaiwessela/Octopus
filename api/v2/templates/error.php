{
	"motd": "<?php $site->title ?? 'Site' ?> Blog API v1 - failed."
	"code": ""

	<?php if(isset($exception)){ ?>
	"error": {
		"class": "<?= get_class($exception) ?>"
		"message": "<?= $exception->getMessage() ?>"
		"code": "<?= $exception->getCode() ?>"
		"file": "<?= $exception->getFile() ?>"
		"line": "<?= $exception->getLine() ?>"
		"trace": <?= json_encode($exception->getTrace()) ?>
	}
	<?php } else { ?>
	"error": null
	<?php } ?>

	"result": null
}
