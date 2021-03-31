<?php
	$adminconfig = (array) json_decode(file_get_contents(__DIR__ . '/adminconfig.json'));

	$name = explode('/', $server->path)[1] ?? '';
	$config = $adminconfig[$name] ?? null;

	$Controller = $ObjectController ?? null;
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="<?= $server->url ?>/admin/resources/css/style.css">
		<link rel="stylesheet" type="text/css" href="<?= $server->url ?>/admin/resources/css/object-specific.css">
		<title><?= $config->lang->{$Controller?->call->action}?->title ?? 'Startseite' ?> – Admin-Bereich</title>
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

				<?php foreach($adminconfig as $nm => $cfg){ ?>
				<li><a
					<?php if($nm == $name){ ?>class="current" <?php } ?>
					href="<?= $server->url ?>/admin/<?= $nm ?>">
						<?= $cfg->lang->plural ?>
				</a></li>
				<?php } ?>
			</ul>
		</nav>
		<main>

<?php
	if(empty($config)){
		require __DIR__ . '/main.php';

	} else {
		switch($Controller->call->action){
			case 'list': 	?><h1><?= $config->lang->list->title	?></h1><?php break;
			case 'show':	?><h1><?= $config->lang->show->title	?></h1><?php break;
			case 'new':		?><h1><?= $config->lang->new->title		?></h1><?php break;
			case 'edit':	?><h1><?= $config->lang->edit->title	?></h1><?php break;
			case 'delete':	?><h1><?= $config->lang->delete->title	?></h1><?php break;
		}

		if($Controller->call->action == 'list'){
			?><a href="<?= $server->url ?>/admin/<?= $name ?>/new" class="button new green">
				<?= $config->lang->new->linktext ?>
			</a><?php
		} else {
			?><a href="<?= $server->url ?>/admin/<?= $name ?>" class="button back">
				<?= $config->lang->list->linktext ?>
			</a><?php
		}

		if($Controller->status('created')){
			?><div class="message green"><?= $config->lang->message->created ?></div><?php
		} else if($Controller->status('edited')){
			?><div class="message green"><?= $config->lang->message->edited ?></div><?php
		} else if($Controller->status('deleted')){
			?><div class="message green"><?= $config->lang->message->deleted ?></div><?php
		} else if($controller->call->action == 'list' && $Controller->status('empty')){
			?><div class="message green"><?= $config->lang->message->empty ?></div><?php
		} else if($Controller->status('unprocessable')){
			?><div class="message red">Die hochgeladenen Daten sind fehlerhaft!</div>
			<ul><?php foreach($Controller->errors->export() as $error){ ?>
				<li><code><?= $error['field'] ?></code>: <?= $error['type'] ?></li>
			<?php } ?></ul><?php
		}

		if($Controller->call->action != 'list' && $Controller->call->action != 'new'){
			?><div><?php

			if($Controller->call->action != 'show'){
				?><a class="button blue"
					href="<?= $server->url ?>/admin/<?= $name ?>/<?= $Object->id ?>">
						Ansehen
				</a><?php
			}

			if($Controller->call->action != 'edit'){
				?><a class="button yellow"
					href="<?= $server->url ?>/admin/<?= $name ?>/<?= $Object->id ?>/edit">
						Bearbeiten
				</a><?php
			}

			if($controller->call->action != 'delete'){
				?><a class="button red"
					href="<?= $server->url ?>/admin/<?= $name ?>/<?= $Object->id ?>/delete">
						Entfernen
				</a><?php
			}

			?></div><?php
		}
	}

	if($Controller?->call->action == 'list' && $Controller?->status('found')){
		$pagination = $Controller->pagination;

		?>
		<p>
			Angezeigt werden <?= $config->lang->plural_nominativ ?>
			<b><?= $pagination->current_item()?->first_object_number() ?> bis
				<?= $pagination->current_item()?->last_object_number() ?></b>
			von insgesamt <b><?= $pagination->total_objects ?></b>
			<?= $config->lang->plural_dativ ?>
		</p>
		<div>
			<?php foreach($pagination->items as $item){ ?>
			<a class="button<?= $item->is_current() ? '' : ' gray' ?>" href="<?= $item->href() ?>">
				<?= $item->number ?>
			</a>
			<?php } ?>
		</div>

		<section class="<?= $name ?> list"><?php
		$Object->each(function($obj) use ($server, $name){ require __DIR__ . '/' . $name . '/list.php'; });
		?></section><?php
	}

	if($Controller?->call->action == 'show' && $Controller?->status('found')){
		require __DIR__ . '/' . $name . '/show.php';
	}

	if(	($Controller?->call->action == 'edit' && !$Controller?->status('edited'))
	||	($Controller?->call->action == 'new' && !$Controller?->status('created')) ){

		require __DIR__ . '/' . $name . '/edit.php';
	}

	if($Controller?->call->action == 'delete' && !$Controller?->status('deleted')){
		require __DIR__ . '/' . $name . '/delete.php';
	}

	// TODO include scripts
?>

		</main>

		<footer>
			<p>
				Diese Seite nutzt »Blog« von Kai Florian Wessela in der Version <?= $server->version ?>.<br>
				Diese Software ist freie Software, lizensiert unter MIT-Lizenz und abrufbar unter
				<a href="https://github.com/kaiwessela/blog">github.com/kaiwessela/blog</a>.<br>
				Copyright © 2020 Kai Florian Wessela – <a href="https://wessela.eu">wessela.eu</a>
			</p>
		</footer>

		<script src="<?= $server->url ?>/admin/resources/js/GetClass.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObject.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Image.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Application.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Person.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Group.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Post.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/DataObjects/Column.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/Modal.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/Pagination.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/SelectModal.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/MultiSelectModal.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/UploadModal.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/PseudoInput.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/Relation.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/RelationInput.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/ListInput.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/TimeInput.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/invoke.js"></script>
		<script src="<?= $server->url ?>/admin/resources/js/validate.js"></script>
	</body>
</html>
