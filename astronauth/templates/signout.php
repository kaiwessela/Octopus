<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include __DIR__ . '/../components/head.php'; ?>
		<title>Abmelden – Astronauth</title>
	</head>
	<body>
		<main>
			<h1>Abmelden</h1>

<?php if($astronauth->is_authenticated()){ ?>
			<form action="#" method="post">
				<input type="hidden" name="logout" value="true">
				<input type="submit" value="Abmelden">
			</form>
<?php } ?>

<?php if($signout_success === true){ ?>
			<section class="message success">
				<p class="summary">Abmeldung erfolgreich</p>
				<p>Sie werden jeden Moment weitergeleitet.</p>
			</section>
<?php } ?>

<?php if($authstate_redirect){ ?>
			<section class="message">
				<p class="summary">Sie werden zur Anmeldung weitergeleitet…</p>
				<p>
					…da Sie abgemeldet sind. Sollten Sie nicht innerhalb von wenigen
					Sekunden weitergeleitet sein, klicken Sie bitte auf diesen Link:
					<a href="<?= $server->url ?>/signin">Weiter zur Anmeldeseite</a>
				</p>
			</section>
<?php } ?>

		</main>
	</body>
</html>
