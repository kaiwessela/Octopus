<section class="events show">
	<code><?= $Event->longid ?></code>
	<h1><?= $Event->title ?></h1>
	<p><?= $Event->timestamp?->format('datetime_long') ?></p>
	<p><?= $Event->location ?></p>
</section>
