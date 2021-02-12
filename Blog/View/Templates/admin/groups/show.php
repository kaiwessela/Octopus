<section class="groups show">
	<code><?= $Group->longid ?></code>
	<h1><?= $Group->name ?></h1>
	<p><?= $Group->description ?></p>

	<h2>Mitglieder:</h2>
	<ul>
	<?php foreach($Group->personrelations as $rel){ ?>
		<li><code><?= $rel->person->longid ?></code> <strong><?= $rel->person->name ?></strong></li>
	<?php } ?>
</ul>
</section>
