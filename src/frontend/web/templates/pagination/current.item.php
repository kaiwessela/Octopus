<?php /* <select class="pagination-item default <?php if($item->disabled){ ?>disabled<?php } ?>" title="<?= $item->title ?>">
	<option>Aktuelle Seite</option>
<?php for($i = 1; $i <= $pagination->page_count; $i++){ ?>
	<option>Seite <?= $i ?></option>
<?php } ?>
</select> */ ?>

<div class="pagination-item current <?php if($item->disabled){ ?>disabled<?php } ?>">
	<?= $item->absolute_number ?>
</div>
