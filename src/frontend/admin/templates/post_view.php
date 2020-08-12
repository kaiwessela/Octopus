<?php
use \Blog\Config\Config;
?>

<h1>Post ansehen</h1>

<?php if($controller->show_err_not_found){ ?>
<span class="message error">
	Post nicht vorhanden.
</span>
<p>Details: <span class="code"><?= $controller->err_not_found_msg ?></span></p>
<?php } ?>

<?php if($controller->show_post){ ?>
<a href="<?= Config::SERVER_URL ?>/admin/posts">Zurück zu allen Posts</a><br>
<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $controller->post->id ?>/edit" class="button">Post bearbeiten</a>
<a href="<?= Config::SERVER_URL ?>/admin/posts/<?= $controller->post->id ?>/delete" class="button">Post löschen</a><br><br>

<article>
	<table>
		<tr>
			<td>Post-URL</td>
			<td><span class="code"><?= $controller->post->longid ?></span></td>
		</tr>
		<tr>
			<td>Overline</td>
			<td class="overline"><?= $controller->post->overline ?></td>
		</tr>
		<tr>
			<td>Headline</td>
			<td class="headline"><?= $controller->post->headline ?></td>
		</tr>
		<tr>
			<td>Subline</td>
			<td class="subline"><?= $controller->post->subline ?></td>
		</tr>
		<tr>
			<td>Teaser</td>
			<td class="teaser"><?= $controller->post->teaser ?></p>
		</tr>
		<tr>
			<td>Autor und Datum</td>
			<td class="author">Von <?= $controller->post->author ?> &middot; <?= //to_date_and_time($controller->post->timestamp) ?></td>
		</tr>

		<?php if(!$controller->post->image->is_empty())){ ?>
		<tr>
			<td>Bild</td>
			<td><img src="<?= Config::SERVER_URL . Config::DYN_IMG_PATH . $controller->post->image->longid?>.<?= $controller->post->image->extension ?>?size=large"
				alt="<?= $controller->post->image->description ?>"></td>
		</tr>
		<?php } ?>

		<tr>
			<td>Inhalt</td>
			<td class="content"><?= $controller->post->content ?></td>
		</tr>
	</table>
</article>
<?php } ?>
