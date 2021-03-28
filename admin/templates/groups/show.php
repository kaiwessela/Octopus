<section class="groups show">
	<code><?= $Object->longid ?></code>
	<h1><?= $Object->name ?></h1>
	<p><?= $Object->description ?></p>

	<h2>Mitglieder:</h2>
	<ul>
	<?php foreach($Object->personrelations as $rel){ ?>
		<li><code><?= $rel->person->longid ?></code> <strong><?= $rel->person->name ?></strong></li>
	<?php } ?>
</ul>
</section>
