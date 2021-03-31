<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Fehler 404 – <?= $site->title ?></title>
	</head>
	<body>
		<?php include 'components/header.php'; ?>
		<main>
			<section>
				<header class="highlighted">
					<h1>Seite nicht gefunden (Fehler 404)</h1>
				</header>
				<p>
					Es tut uns leid, aber es scheint, als sei diese Seite nicht vorhanden.<br>
					<a href="<?= $server->url ?>">Über diesen Link gelangen Sie zur
					Startseite zurück</a>. Wenn Sie den Verdacht haben, dass ein technischer
					Fehler aufgetreten ist, kontaktieren Sie uns bitte.
				</p>
			</section>
		</main>
		<?php include 'components/footer.php'; ?>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
