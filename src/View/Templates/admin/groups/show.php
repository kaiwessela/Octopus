<article>
	<code><?= $obj->longid ?></code>
	<h1><?= $obj->name ?></h1>
	<p><?= $obj->description ?></p>

	<h2>Mitglieder:</h2>
	<ul>
	<?php foreach($obj->persons as $person){ ?>
		<li><code><?= $person->longid ?></code> <strong><?= $person->name ?></strong></li>
	<?php } ?>
</ul>
</article>
