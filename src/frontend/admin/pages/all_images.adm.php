<?php
try {
	$objs = Image::pull_all();
} catch(ObjectNotFoundException $e){
	$objs = false;
}

?>
<h1>Alle Bilder</h1>
<a href="new_image">Neues Bild hochladen</a>
<table>
	<tr>
		<th>URL</th>
		<th>Erweiterung</th>
		<th>Aktionen</th>
	</tr>
	<?php
		if($objs === false){
			?>
			<tr><td>Keine Bilder vorhanden</td></tr>
			<?php
		} else {
			foreach($objs as $obj){
				?>
				<tr>
					<td><?php echo $obj->longid; ?></td>
					<td><?php echo $obj->extension; ?></td>
					<td>
						<a href="view_image?id=<?php echo $obj->id; ?>">Ansehen</a>
						<a href="edit_image?id=<?php echo $obj->id; ?>">Bearbeiten</a>
						<a href="delete_image?id=<?php echo $obj->id; ?>">Löschen</a>
					</td>
				</tr>
				<?php
			}
		}
	?>
</table>
