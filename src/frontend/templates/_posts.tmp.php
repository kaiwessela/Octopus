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
			<p><b>Seite 1 von x</b> – Angezeigt werden Artikel 1 bis 10 von insgesamt 500 Artikeln</p>

<?php
$posts = Post::pull_all();
foreach($posts as $post){

			include COMPONENT_PATH . 'preview-post.comp.php';

}
?>

		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
	</body>
</html>
