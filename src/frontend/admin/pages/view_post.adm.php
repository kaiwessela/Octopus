<h1>Post ansehen</h1>

<?php
if(isset($_GET['id'])){
	try {
		$obj = Post::pull_by_id($_GET['id']);
	} catch(ObjectNotFoundException $e){
		$obj = false;
	}
} else {
	$obj = false;
}

if($obj == false){
	?>

	<span class="message error">
		Post nicht vorhanden.
	</span>

	<?php
} else {
	?>

	<a href="all_posts">Zurück zu allen Posts</a><br>
	<a href="edit_post?id=<?php echo $obj->id; ?>" class="button">Post bearbeiten</a>
	<a href="delete_post?id=<?php echo $obj->id; ?>" class="button">Post löschen</a><br><br>

	<article>
		<table>
			<tr>
				<td>Post-URL</td>
				<td><span class="code"><?php echo $obj->longid; ?></span></td>
			</tr>
			<tr>
				<td>Overline</td>
				<td class="overline"><?php echo $obj->overline; ?></td>
			</tr>
			<tr>
				<td>Headline</td>
				<td class="headline"><?php echo $obj->headline; ?></td>
			</tr>
			<tr>
				<td>Subline</td>
				<td class="subline"><?php echo $obj->subline; ?></td>
			</tr>
			<tr>
				<td>Teaser</td>
				<td class="teaser"><?php echo $obj->teaser; ?></p>
			</tr>
			<tr>
				<td>Autor und Datum</td>
				<td class="author">Von <?php echo $obj->author; ?> &middot; <?php echo to_date_and_time($obj->timestamp); ?></td>
			</tr>

			<?php
			if(isset($obj->image)){
				?>
				<tr>
					<td>Bild</td>
					<td><img src="/resources/images/dynamic/
						<?php echo $obj->image->longid . '.' . $obj->image->extension;?>?size=large"
						alt="<?php echo $obj->image->description; ?>"></td>
				</tr>
				<?php
			}
			?>

			<tr>
				<td>Inhalt</td>
				<td class="content"><?php echo $obj->content; ?></td>
			</tr>
		</table>
	</article>

	<?php
}
?>
