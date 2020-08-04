<?php
try {
	$objs = Image::pull_all();
} catch(EmptyResultException $e){
	$objs = false;
}
?>

<h1>Alle Bilder</h1>

<?php
if($objs == false){
	?>
	<span class="message warning">
		Bisher sind keine Bilder vorhanden.
	</span>
	<?php
}
?>

<a href="<?= ADMIN_URL ?>/new_image" class="button">Neues Bild hochladen</a>

<?php
if($objs != false){
	?>
	<table>
		<tr>
			<th>URL</th>
			<th>Dateityp</th>
			<th>Aktionen</th>
		</tr>
		<?php
		foreach($objs as $obj){
			?>
			<tr>
				<td><span class="code"><?= $obj->longid ?></span></td>
				<td><span class="code"><?= $obj->extension ?></span></td>
				<td>
					<a href="<?= ADMIN_URL ?>/view_image?id=<?= $obj->id ?>">Ansehen</a>
					<a href="<?= ADMIN_URL ?>/edit_image?id=<?= $obj->id ?>">Bearbeiten</a>
					<a href="<?= ADMIN_URL ?>/delete_image?id=<?= $obj->id ?>">Löschen</a>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>
