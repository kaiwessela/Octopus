<form action="#" method="post" class="columns edit">

<?php if($ColumnController->request->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Rubrik-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Rubrik-ID wird in der URL verwendet und entspricht oftmals ungefähr dem Namen.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Column?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Column?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Column?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- NAME -->
	<label for="name">
		<span class="name">Name</span>
		<span class="conditions">erforderlich, 1 bis 30 Zeichen</span>
		<span class="infos">
			Der Name der Rubrik.
		</span>
	</label>
	<input type="text" size="30"
		id="name" name="name" value="<?= $Column?->name ?>"
		maxlength="30" required>


	<!-- DESCRIPTION -->
	<label for="description">
		<span class="name">Beschreibung</span>
		<span class="conditions">optional</span>
		<span class="infos">
			Die Beschreibung der Rubrik.
		</span>
	</label>
	<textarea id="description" name="description"
		cols="50" rows="3"><?= $Column?->description ?></textarea>


	<!-- POSTS -->
	<label>
		<span class="name">Artikel</span>
		<span class="conditions">optional, Mehrfacheintrag nicht erlaubt</span>
		<span class="infos">
			Änderungen werden lokal zwischengespeichert und beim Abschicken übernommen.
		</span>
	</label>
	<div class="relationinput nojs" data-type="Post" data-unique="true" data-for="posts" data-selectmodal="posts-select">
		<div class="objects">
			<?php $Column?->postrelations?->foreach(function($i, $rel) use ($Column){ ?>
				<div class="relation" data-i="<?= $i ?>" data-exists="true">
					<input type="hidden" name="postrelations[<?= $i ?>][id]" value="<?= $rel->id ?>">
					<input type="hidden" name="postrelations[<?= $i ?>][action]" class="action" value="ignore">
					<input type="hidden" name="postrelations[<?= $i ?>][post_id]" class="objectId" value="<?= $rel->post->id ?>">
					<input type="hidden" name="postrelations[<?= $i ?>][column_id]" value="<?= $Column?->id ?>">
					<p class="title"><span><?= $rel->post->headline ?></span> – <code><?= $rel->post->longid ?></code></p>
					<button type="button" class="red" data-action="remove">Entfernen</button>
					<button type="button" data-action="restore">Entf. rückgängig</button>
				</div>
			<?php }); ?>
			<template>
				<div class="relation" data-i="{{i}}" data-exists="false">
					<input type="hidden" name="postrelations[{{i}}][action]" class="action" value="new">
					<input type="hidden" name="postrelations[{{i}}][post_id]" class="objectId" value="{{id}}">
					<input type="hidden" name="postrelations[{{i}}][column_id]" value="<?= $Column?->id ?>">
					<p class="title"><span>{{headline}}</span> – <code>{{longid}}</code></p>
					<button type="button" class="red" data-action="remove">Entfernen</button>
				</div>
			</template>
		</div>
		<button type="button" class="new blue" data-action="select">Artikel hinzufügen</button>
	</div>


	<button type="submit" class="green">Speichern</button>
</form>

<div class="modal multiselectmodal nojs" data-name="posts-select" data-type="Post" data-objectsperpage="20">
	<div class="box">
		<h2>Artikel auswählen</h2>
		<form action="#" method="GET">
			<section class="objects">
				<template>
					<article>
						<label class="checkbodge turn-around">
							<span class="label-field">{{headline}} – {{longid}}</span>
							<input type="checkbox" name="result" value="{{id}}">
							<span class="bodgecheckbox">
								<span class="bodgetick">
									<span class="bodgetick-down"></span>
									<span class="bodgetick-up"></span>
								</span>
							</span>
						</label>
					</article>
				</template>
			</section>
			<button type="button" data-action="loadmore">Weitere Artikel laden</button><br>
			<button type="submit" data-action="submit" class="blue">Auswählen</button>
			<button type="button" data-action="close" class="red">Schließen</button>
		</form>
	</div>
</div>
