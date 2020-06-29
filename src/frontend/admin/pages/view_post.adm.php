<h1>Post ansehen</h1>

<?php
if(isset($_GET['id'])){
	try {
		$obj = Post::pull_by_id($_GET['id']);
	} catch(EmptyResultException $e){
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

	<a href="<?= ADMIN_URL ?>/all_posts">Zurück zu allen Posts</a><br>
	<a href="<?= ADMIN_URL ?>/edit_post?id=<?= $obj->id ?>" class="button">Post bearbeiten</a>
	<a href="<?= ADMIN_URL ?>/delete_post?id=<?= $obj->id ?>" class="button">Post löschen</a><br><br>

	<article>
		<table>
			<tr>
				<td>Post-URL</td>
				<td><span class="code"><?= $obj->longid ?></span></td>
			</tr>
			<tr>
				<td>Overline</td>
				<td class="overline"><?= $obj->overline ?></td>
			</tr>
			<tr>
				<td>Headline</td>
				<td class="headline"><?= $obj->headline ?></td>
			</tr>
			<tr>
				<td>Subline</td>
				<td class="subline"><?= $obj->subline ?></td>
			</tr>
			<tr>
				<td>Teaser</td>
				<td class="teaser"><?= $obj->teaser ?></p>
			</tr>
			<tr>
				<td>Autor und Datum</td>
				<td class="author">Von <?= $obj->author ?> &middot; <?= to_date_and_time($obj->timestamp) ?></td>
			</tr>

			<?php
			if(isset($obj->image)){
				?>
				<tr>
					<td>Bild</td>
					<td><img src="<?= DYN_IMG_PATH . $obj->image->longid . '.' . $obj->image->extension ?>?size=large"
						alt="<?= $obj->image->description ?>"></td>
				</tr>
				<?php
			}
			?>

			<tr>
				<td>Inhalt</td>
				<td class="content"><?= $obj->content ?></td>
			</tr>
		</table>
	</article>

	<?php
}
?>
