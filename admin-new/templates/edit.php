<?php $entityname = $AC->get_entity_name(); ?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<?php include 'components/head.php'; ?>
		<title>Artikel-Editor – OctopusAdmin</title>
	</head>
	<body>
		<header>
			<?php include 'components/logo.php'; ?>
			<h1>Artikel-Editor</h1>
			<!--<div class="editor-mode" data-nojs="off">
				Benutzerfreundlich
				Schick
			</div>-->
			<?php include 'components/login.php'; ?>
		</header>
		<nav>
			<?php include 'components/nav.php'; ?>
		</nav>
		<main>
			<div id="messages">
				<?php
				$action = $EntityController->get_action();
				$status = $EntityController->get_status_code();

				if($status === 422){
					?>
					<div class="message error">Speichern fehlgeschlagen! Die gesendeten Daten sind fehlerhaft.</div>
					<?php
				} else if($action === 'edit' && $status === 200){
					?>
					<div class="message success">Speichern erfolgreich.</div>
					<?php
				}
				?>
			</div>

			<?php $interactive_forms = []; ?>

			<form action="#" method="post" class="editor" enctype="multipart/form-data">
				<?php include "entities/{$entityname}/edit.php" ?>

				<button type="submit">Speichern</button>
			</form>

			<?php foreach($interactive_forms as $inf){ include "entities/{$inf}.php"; } ?>
		</main>
		<?php include 'components/scripts.php'; ?>
	</body>
</html>
