{
	"motd": "<?= $site->title ?> Blog API v1 - running.",
	"code": "200 OK", <?php // TODO without OK ?>
	"result": <?php
		if(empty($ObjectController)){
			echo 'null';
		} else if($ObjectController?->call->action == 'count'){
			echo json_encode($Object?->count());
		} else {
			echo json_encode($Object?->staticize());
		}
	?>
}
