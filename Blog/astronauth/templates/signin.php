<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include __DIR__ . '/../components/head.php'; ?>
		<title>Anmelden – Astronauth</title>
	</head>
	<body>
		<main>
			<h1>Anmelden</h1>

<?php if(!$astronauth->is_authenticated()){ ?>
			<a href="<?= $server->url ?>/signup">Neu hier? Registrieren</a>
<?php } ?>

<?php if($signin_success === false){ ?>
			<section class="message error">
				<p class="summary">Anmeldung fehlgeschlagen</p>
				<p>Benutzer existiert nicht oder Passwort falsch.</p>
			</section>
<?php } ?>

<?php if(!$astronauth->is_authenticated()){ ?>
			<form action="#" method="post">
				<label for="identifier">Benutzername oder E-Mail-Adresse</label>
				<input type="text" id="identifier" name="identifier" required>
				<label for="password">Passwort</label>
				<input type="password" id="password" name="password" required>
				<a href="forgotPassword" class="under-input">Passwort vergessen?</a>
				<label class="checkbodge turn-around">
					<span class="label-field">Angemeldet bleiben</span>
					<input type="checkbox" id="remember" name="remember" value="true">
					<span class="bodgecheckbox">
						<span class="bodgetick">
							<span class="bodgetick-down"></span>
							<span class="bodgetick-up"></span>
						</span>
					</span>
				</label>
				<input type="submit" value="Anmelden">
			</form>
<?php } ?>

<?php if($signin_success === true){ ?>
			<section class="message success">
				<p class="summary">Anmeldung erfolgreich</p>
				<p>Sie werden jeden Moment weitergeleitet.</p>
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
