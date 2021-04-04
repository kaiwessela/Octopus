<article>
	<code><?= $obj->longid ?></code>
	<h2><?= $obj->title ?></h2>
	<p>Sitzung am <?= $obj->timestamp->format('date_short') ?></p>
	<span class="tag <?= match($obj->status){'draft' => 'blue', 'accepted' => 'green', 'rejected' => 'red'} ?>">
		<?= match($obj->status){
			'draft' => 'Entwurf',
			'accepted' => 'Angenommen',
			'rejected' => 'Abgelehnt'
		} ?>
	</span>
	<div>
		<a class="button blue"
			href="<?= $server->url ?>/admin/motions/<?= $obj->id ?>">Ansehen</a>
		<a class="button yellow"
			href="<?= $server->url ?>/admin/motions/<?= $obj->id ?>/edit">Bearbeiten</a>
		<a class="button red"
			href="<?= $server->url ?>/admin/motions/<?= $obj->id ?>/delete">Löschen</a>
	</div>
</article>
