<?php
try {
	$objs = Image::pull_all();
} catch(ObjectNotFoundException $e){
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

<a href="new_image" class="button">Neues Bild hochladen</a>

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
				<td><span class="code"><?php echo $obj->longid; ?></span></td>
				<td><span class="code"><?php echo $obj->extension; ?></span></td>
				<td>
					<a href="view_image?id=<?php echo $obj->id; ?>">Ansehen</a>
					<a href="edit_image?id=<?php echo $obj->id; ?>">Bearbeiten</a>
					<a href="delete_image?id=<?php echo $obj->id; ?>">Löschen</a>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>
