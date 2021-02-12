<article class="event<?php if($event->cancelled){ ?> cancelled<?php } ?>" data-organisation="<?= $event->organisation ?>">
	<p class="organisation"><?= $event->organisation ?></p>
	<h3 class="title"><?= $event->title ?></h3>
	<p class="date-and-time"><?= $event->timestamp->format('datetime_long') ?></p>
	<p class="location"><?= $event->location ?></p>
	<p class="description"><?= $event->description ?></p>
</article>
