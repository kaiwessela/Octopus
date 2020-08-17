<?php
use \Blog\Frontend\Web\SiteConfig;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
<<<<<<< HEAD:src/frontend/web/templates/default.tmp.php
		<title><?= $title ?> – <?= SiteConfig::TITLE ?></title>
=======
		<title><?= $StaticController->title ?> – <?= SiteConfig::TITLE ?></title>
>>>>>>> 22066990995bdc7b197e3d6a0a8cb817f13da61c:src/frontend/web/templates/static.tmp.php
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<?= $StaticController->content ?>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
