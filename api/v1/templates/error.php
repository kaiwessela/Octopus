{
	"motd": "<?= $site->title ?? 'Site' ?> Blog API v1 - failed.",
	"code": "",

	<?php if(isset($exception)){ ?>
	"error": {
		"class": <?= json_encode(get_class($exception)) ?>,
		"message": <?= json_encode($exception->getMessage()) ?>,
		"code": <?= json_encode($exception->getCode()) ?>,
		"file": <?= json_encode($exception->getFile()) ?>,
		"line": <?= json_encode($exception->getLine()) ?>,
		"trace": <?= json_encode($exception->getTrace()) ?>
	},
	<?php } else { ?>
	"error": null,
	<?php } ?>

	"result": null
}
