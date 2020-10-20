<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.comp.php'; ?>
		<title><?= $Page->object->title ?> – <?= $site->title ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.comp.php'; ?>
		<main>
			<?= $Page->object->content->parsed ?>
		</main>
		<?php include COMPONENT_PATH . 'footer.comp.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.comp.php'; ?>
	</body>
</html>
