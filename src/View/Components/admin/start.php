<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= $server->url ?>/resources/css/admin.css">
	</head>
	<body>
		<header>
			<div class="title">
				<?= $site->title ?><span class="darkened"> – Admin</span>
			</div>
			<div class="astronauth">
				🚀 <span class="darkened">Angemeldet als</span> <?= $astronauth->get_account_name() ?>
				<div class="expand">
					<a href="<?= $server->url ?>/astronauth/account" class="button blue">Account verwalten</a>
					<a href="<?= $server->url ?>/astronauth/signout" class="button red">Abmelden</a>
				</div>
			</div>
		</header>
		<nav>
			<ul>
				<li><a <?php if($server->path == 'admin'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin">Startseite</a></li>
				<li><a <?php if($server->path == 'admin/pages'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/pages">Seiten</a></li>
				<li><a <?php if($server->path == 'admin/posts'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/posts">Posts</a></li>
				<li><a <?php if($server->path == 'admin/columns'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/columns">Rubriken</a></li>
				<li><a <?php if($server->path == 'admin/images'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/images">Bilder</a></li>
				<li><a <?php if($server->path == 'admin/persons'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/persons">Personen</a></li>
				<li><a <?php if($server->path == 'admin/groups'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/groups">Gruppen</a></li>
				<li><a <?php if($server->path == 'admin/events'){ ?>class="current" <?php } ?>href="<?= $server->url ?>/admin/events">Veranstaltungen</a></li>
			</ul>
		</nav>
