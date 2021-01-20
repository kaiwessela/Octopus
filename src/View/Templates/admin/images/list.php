<article>
	<a href="<?= $server->url ?>/admin/images/<?= $obj->id ?>">
		<img src="<?= $obj->src() ?>" alt="<?= $obj->description ?>">
		<code><?= $obj->longid ?></code>
	</a>
</article>
