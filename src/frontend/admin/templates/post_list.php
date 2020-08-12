<?php
use \Blog\Config\Config;
?>

<h1>Alle Posts</h1>

<?php if($controller->show_warn_no_found){ ?>
<span class="message warning">
	Bisher sind keine Posts vorhanden.
</span>
<?php } ?>

<a href="<?= Config::SERVER_URL ?>/admin/posts/new" class="button">Neuen Post schreiben</a>

<?php if($controller->show_list){ ?>
<table>
	<tr>
		<th>Überschrift</th>
		<th>URL</th>
		<th>Aktionen</th>
	</tr>

	<?php foreach($controller->posts as $post){ ?>
	<tr>
		<td><span class="code"><?= $post->headline ?></span></td>
		<td><span class="code"><?= $post->longid ?></span></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>">Ansehen</a></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/edit">Bearbeiten</a></td>
		<td><a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $post->id ?>/delete">Löschen</a></td>
	</tr>
	<?php } ?>

</table>
<?php } ?>
