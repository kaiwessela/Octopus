<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include COMPONENT_PATH . 'head.php'; ?>
		<title>Fehler 404 – <?= $site->title ?></title>
	</head>
	<body>
		<?php include COMPONENT_PATH . 'header.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Fehler 404</h1>
				</header>
				<p>
					Es tut uns leid, aber es scheint, als sei diese Seite nicht vorhanden.<br>
					<a href="<?= $server->url ?>">Über diesen Link gelangen Sie zur
					Startseite zurück</a>. Wenn Sie den Verdacht haben, dass ein technischer
					Fehler aufgetreten ist, kontaktieren Sie uns bitte.
				</p>
			</section>
		</main>
		<?php include COMPONENT_PATH . 'footer.php'; ?>
		<?php include COMPONENT_PATH . 'scripts.php'; ?>
	</body>
</html>
