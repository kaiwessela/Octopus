<p>
	Zeige Objekte <b><?= $pagination->first_object ?> bis <?= $pagination->last_object ?></b>
	von insgesamt <b><?= $pagination->total_objects ?></b> Objekten
</p>
<div>
	<?php foreach($pagination->items as $item){ ?>
	<a class="button<?php if($item->template != 'current'){ ?> gray<?php } ?>"
		href="<?= $server->url ?>/<?= $pagination->base_path ?>/<?= $item->target ?>">
		<?= $item->target ?>
	</a>
	<?php } ?>
</div>
