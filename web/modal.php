<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="resources/css/modal.css">
	</head>
	<body>
		<form>
			<label>Dings</label>
			<div class="pseudoinput relationlist">
				<div class="objectbox" data-count="0"></div>
				<template>
					<div class="listitem">
						<p><strong>{{headline}}</strong><code>{{longid}}</code></p>
						<input type="hidden" name="relations[{{i}}][post_id]" value="{{id}}">
						<input type="hidden" name="relations[{{i}}][column_id]" value="?php?">

						<input type="radio" name="relations[{{i}}][action]" value="nothing">
						<input type="radio" name="relations[{{i}}][action]" value="new" checked>
						<input type="radio" name="relations[{{i}}][action]" value="edit" disabled>
						<input type="radio" name="relations[{{i}}][action]" value="delete" disabled>
					</div>
				</template>
				<button type="button" data-modal="test" data-action="open">Hinzufügen</button>
			</div>
		</form>

		<div class="modal selectmodal" data-name="test">
			<div class="box">
				<h2>Auswählen</h2>
				<form action="#" method="GET">
					<div class="objectbox"></div>
					<template>
						<label>
							<input type="radio" name="result" value="{{id}}" {{selected}}>
							<h3>{{headline}}</h3>
							<code>{{longid}}</code>
						</label>
					</template>

					<button type="button" data-action="close">Schließen</button>
					<button type="submit" data-action="submit">Auswählen</button>
				</form>
			</div>
		</div>


		<script src="resources/js/newadmin/post.js"></script>
		<script src="resources/js/newadmin/modal.js"></script>
		<script src="resources/js/newadmin/selectmodal.js"></script>
		<script src="resources/js/newadmin/invoke.js"></script>
		<script>
			modals['test'].type = 'post';
			modals['test'].onSubmit = () => {
				var count = Number(document.querySelector('.relationlist .objectbox').getAttribute('data-count'));
				count++;
				document.querySelector('.relationlist .objectbox').setAttribute('data-count', count);

				var newcontent = modals['test'].valueObject.replace(document.querySelector('.relationlist template').innerHTML);
				newcontent = newcontent.replace(/{{i}}/g, count);
				document.querySelector('.relationlist .objectbox').innerHTML += newcontent;
			}
		</script>
	</body>
</html>
