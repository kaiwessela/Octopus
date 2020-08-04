<?php
try {
	$objs = Post::pull_all();
} catch(EmptyResultException $e){
	$objs = false;
}
?>

<h1>Alle Posts</h1>

<?php
if($objs == false){
	?>
	<span class="message warning">
		Bisher sind keine Posts vorhanden.
	</span>
	<?php
}
?>

<a href="<?= ADMIN_URL ?>/new_post" class="button">Neuen Post hinzufügen</a>

<?php
if($objs != false){
	?>
	<table>
		<tr>
			<th>Überschrift</th>
			<th>URL</th>
			<th>Aktionen</th>
		</tr>
		<?php
		foreach($objs as $obj){
			?>
			<tr>
				<td><?= $obj->headline ?></td>
				<td><span class="code"><?= $obj->longid ?></span></td>
				<td>
					<a href="<?= ADMIN_URL ?>/view_post?id=<?= $obj->id ?>">Ansehen</a>
					<a href="<?= ADMIN_URL ?>/edit_post?id=<?= $obj->id ?>">Bearbeiten</a>
					<a href="<?= ADMIN_URL ?>/delete_post?id=<?= $obj->id ?>">Löschen</a>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>
