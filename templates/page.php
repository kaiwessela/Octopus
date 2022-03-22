<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title><?= $Page->title ?> – Kai Florian Wessela</title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<?= $Page->content?->parse() ?>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
