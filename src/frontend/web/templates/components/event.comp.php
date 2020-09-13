<?php
use \Blog\Config\Config;
use \Blog\Frontend\Web\Modules\TimeFormat;
?>
<article class="event <?php if($event->cancelled){ echo 'cancelled'; } ?>" data-organisation="<?= $event->organisation ?>">
	<p class="organisation"><?= $event->organisation ?></p>
	<h3 class="title"><?= $event->title ?></h3>
	<p class="date-and-time"><?= TimeFormat::date_and_time_with_day($event->timestamp) ?></p>
	<p class="location"><?= $event->location ?></p>
	<p class="description"><?= $event->description ?></p>
</article>
