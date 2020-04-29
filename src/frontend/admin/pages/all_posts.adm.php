<?php
try {
	$objs = Post::pull_all();
} catch(ObjectNotFoundException $e){
	$objs = false;
}

?>
<h1>Alle Posts</h1>
<a href="new_post">Neuen Post schreiben</a>
<table>
	<tr>
		<th>Überschrift</th>
		<th>ID</th>
		<th>Aktionen</th>
	</tr>
	<?php
		if($objs === false){
			?>
			<tr><td>Keine Posts vorhanden</td></tr>
			<?php
		} else {
			foreach($objs as $obj){
				?>
				<tr>
					<td><?php echo $obj->headline; ?></td>
					<td><?php echo $obj->id; ?></td>
					<td>
						<a href="view_post?id=<?php echo $obj->id; ?>">Ansehen</a>
						<a href="edit_post?id=<?php echo $obj->id; ?>">Bearbeiten</a>
						<a href="delete_post?id=<?php echo $obj->id; ?>">Löschen</a>
					</td>
				</tr>
				<?php
			}
		}
	?>
</table>
