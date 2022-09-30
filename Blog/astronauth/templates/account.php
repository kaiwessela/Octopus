<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include __DIR__ . '/../components/head.php'; ?>
		<title>Account – Astronauth</title>
	</head>
	<main>
		<h1>Hallo, <?= $astronauth->get_account_name() ?>!</h1>
		<p>Du bist bei Astronauth angemeldet.</p>

		<h2>Dein Account</h2>
		<p>Benutzername: <?= $astronauth->get_account_name() ?></p>
		<p>E-Mail-Adresse: <?= $astronauth->get_account_email() ?></p>

		<h2>Abmelden</h2>
		<a href="<?= $server->url ?>/account/signout">zur Abmeldung</a>
	</main>
</html>
