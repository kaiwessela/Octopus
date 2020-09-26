<header>
	<nav>
		<div>
			<a href="<?= $server->url ?>" class="logo">
				kaiwessela:Blog<span class="darkened">/admin</span>
			</a>
			<a href="<?= $server->url ?>/admin">Startseite</a>
			<a href="<?= $server->url ?>/admin/pages">Seiten</a>
			<a href="<?= $server->url ?>/admin/posts">Posts</a>
			<a href="<?= $server->url ?>/admin/images">Bilder</a>
			<a href="<?= $server->url ?>/admin/persons">Personen</a>
			<a href="<?= $server->url ?>/admin/events">Veranstaltungen</a>
		</div>
		<div class="astronauth">
			<div class="navline">
				<span class="icon">🚀</span>
				<?= $astronauth->account->name ?>
			</div>
			<div class="dropdown">
				<a href="<?= $server->url ?>/astronauth/signout" class="button">Abmelden</a>
			</div>
		</div>
	</nav>
</header>
