<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include __DIR__ . '/../components/head.php'; ?>
		<title>Account erstellen – Astronauth</title>
	</head>
	<body>
		<main>
			<h1>Account erstellen</h1>

<?php if(!$signup_return){ ?>
			<a href="<?= $server->url ?>/signin">Mit bestehendem Account anmelden</a>
<?php } ?>

<?php if(is_string($signup_return)){ ?>
			<section class="message error">
				<p class="summary">Registrierung fehlgeschlagen</p>
				<p><?= $error_message ?></p>
			</section>
<?php } ?>

<?php if(!$signup_return){ ?>
			<form action="#" method="post">
				<label for="name">
					<span class="label-field">Benutzername</span>
					<span class="label-hint">
						4 bis 32 Stellen, erlaubt sind Buchstaben, Zahlen, Punkte (.) und
						Binde- und Unterstriche (- und _).
					</span>
				</label>
				<input type="text" id="name" name="name" required>
				<label for="email">
					<span class="label-field">E-Mail-Adresse</span>
					<span class="label-hint">
						Die E-Mail-Adresse benötigen wir für Dings und Bums.
					</span>
				</label>
				<input type="email" id="email" name="email" required>
				<label for="password">
					<span class="label-field">Passwort</span>
					<span class="label-hint">
						8 bis 128 Zeichen. <a href="#">Tipps für ein sicheres Passwort</a>
					</span>
				</label>
				<input type="password" id="password" name="password" required>
				<input type="submit" value="Account erstellen">
			</form>
<?php } ?>

<?php if($signup_return === true){ ?>
			<section class="message success">
				<p class="summary">Registrierung erfolgreich</p>
				<p>Weiter zur <a href="signin">Anmeldung</a></p>
			</section>
<?php } ?>

<?php if($authstate_redirect){ ?>
			<section class="message">
				<p class="summary">Sie werden weitergeleitet…</p>
				<p>
					…da Sie bereits angemeldet sind. Sollten Sie nicht innerhalb von wenigen
					Sekunden weitergeleitet sein, klicken Sie bitte auf diesen Link:
					<a href="<?= $server->url ?>/account">Weiter zur Hauptseite</a>
				</p>
			</section>
<?php } ?>

		</main>
	</body>
</html>
