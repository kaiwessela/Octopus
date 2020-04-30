<?php
try {
	$objs = Post::pull_all();
} catch(ObjectNotFoundException $e){
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

<a href="new_post" class="button">Neuen Post hinzufügen</a>

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
				<td><?php echo $obj->headline; ?></td>
				<td><span class="code"><?php echo $obj->longid; ?></span></td>
				<td>
					<a href="view_post?id=<?php echo $obj->id; ?>">Ansehen</a>
					<a href="edit_post?id=<?php echo $obj->id; ?>">Bearbeiten</a>
					<a href="delete_post?id=<?php echo $obj->id; ?>">Löschen</a>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>
