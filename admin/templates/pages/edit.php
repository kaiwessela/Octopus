<form action="#" method="post" class="pages edit">

<?php if($Controller->call->action == 'new'){ ?>

	<!-- LONGID -->
	<label for="longid">
		<span class="name">Seiten-ID</span>
		<span class="conditions">
			erforderlich; 9 bis 60 Zeichen, nur Kleinbuchstaben (a-z), Ziffern (0-9) und
			Bindestriche (-)
		</span>
		<span class="infos">
			Die Seiten-ID wird als URL verwendet (<code><?= $server->url ?>/[Seiten-ID]</code>) und
			entspricht oftmals ungefähr dem Titel.
		</span>
	</label>
	<input type="text" size="40" autocomplete="off"
		id="longid" name="longid" value="<?= $Object?->longid ?>"
		minlength="9" maxlength="60" pattern="^[a-z0-9-]*$" required>

<?php } else { ?>

	<label for="id">
		<span class="name">ID</span>
	</label>
	<input type="text" id="id" name="id" value="<?= $Object?->id ?>" size="8" readonly>

	<label for="longid">
		<span class="name">Long-ID</span>
	</label>
	<input type="text" id="longid" name="longid" value="<?= $Object?->longid ?>" size="40" readonly>

<?php } ?>

	<!-- TITLE -->
	<label for="title">
		<span class="name">Titel</span>
		<span class="conditions">erforderlich, 1 bis 60 Zeichen</span>
		<span class="infos">
			Der Titel der Seite steht u.a. im Fenstertitel des Browsers und sollte
			einen Hinweis auf den Inhalt geben.
		</span>
	</label>
	<input type="text" size="40"
		id="title" name="title" value="<?= $Object?->title ?>"
		maxlength="60" required>

	<!-- CONTENT -->
	<label for="content">
		<span class="name">Inhalt</span>
		<span class="conditions">
			optional, HTML und Markdown-Schreibweise möglich
			(<a href="https://de.wikipedia.org/wiki/Markdown">Wikipedia: Markdown</a>)
		</span>
		<span class="infos">Der eigentliche Inhalt der Seite.</span>
	</label>
	<textarea id="content" name="content"
		cols="80" rows="20"><?= $Object?->content ?></textarea>

	<button type="submit" class="blue">Speichern</button>
</form>
