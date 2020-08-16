<?php
use \Blog\Config\Config;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= Config::SERVER_URL ?>/resources/css/admin.css">
	</head>
	<body>
		<header>
			<nav>
				<div>
					<a href="<?= Config::SERVER_URL ?>" class="logo">
						kaiwessela:Blog<span class="darkened">/admin</span>
					</a>
					<a href="<?= Config::SERVER_URL ?>/admin">Startseite</a>
					<a href="<?= Config::SERVER_URL ?>/admin/posts">Posts</a>
					<a href="<?= Config::SERVER_URL ?>/admin/images">Bilder</a>
					<a href="<?= Config::SERVER_URL ?>/admin/persons">Personen</a>
				</div>
				<div class="astronauth">
					<div class="navline">
						<span class="icon">🚀</span>
						<?= $user->account->name ?>
					</div>
					<div class="dropdown">
						<a href="<?= Config::SERVER_URL ?>/astronauth/signout" class="button">Abmelden</a>
					</div>
				</div>
			</nav>
		</header>
		<main>
			<?php $controller->display(); ?>
		</main>
	</body>
</html>
