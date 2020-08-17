<?php
use \Blog\Frontend\Web\SiteConfig;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= SiteConfig::TITLE ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<section class="highlighted">
				<h1>Hallo.</h1>
			</section>
			<section>
				<h2>Neueste Posts</h2>
				<?php
				foreach($PostListController->posts as $post){
					include COMPONENT_PATH . 'preview-post.comp.php';
				}
				?>
			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
