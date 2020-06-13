<?php
$offset = $posts_per_page * ($pagination_current - 1);

$to = $offset + $posts_per_page;
if($to > $post_count){
	$to = $post_count;
}
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title>Alle Artikel – Kai Florian Wessela</title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<div>
				<h1>Alle Artikel</h1>
			</div>
			<p>
				<b>Seite <?= $pagination_current ?> von <?= $pagination_max ?></b>
				– Angezeigt werden Artikel <?= $offset+1 ?> bis <?= $to ?>
				von insgesamt <?= $post_count ?> Artikeln
			</p>

<?php
$posts = Post::pull_all($posts_per_page, $offset);
foreach($posts as $post){

			include COMPONENT_PATH . 'preview-post.comp.php';

}
?>

		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<script src="/resources/js/script.js"></script>
	</body>
</html>
