<section class="events show">
	<code><?= $Object->longid ?></code>
	<h1><?= $Object->title ?></h1>
	<p><?= $Object->timestamp?->format('datetime_long') ?></p>
	<p><?= $Object->location ?></p>
</section>
