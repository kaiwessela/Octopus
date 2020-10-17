<div class="pagination">
	<?php
	foreach($pagination->items as $item){
		if($item->template == 'first'){
			?>
			<a href="./<?= $item->target ?>"
				class="pagination-item first-last <?php if($item->disabled){ ?>disabled<?php } ?>"
				title="<?= $item->title ?>">
				Erste
			</a>
			<?php
		} else if($item->template == 'last'){
			?>
			<a href="./<?= $item->target ?>"
				class="pagination-item first-last <?php if($item->disabled){ ?>disabled<?php } ?>"
				title="<?= $item->title ?>">
				Letzte
			</a>
			<?php
		} else if($item->template == 'current'){
			?>
			<div class="pagination-item current <?php if($item->disabled){ ?>disabled<?php } ?>">
				<?= $item->target ?>
			</div>
			<?php
		} else {
			?>
			<a href="./<?= $item->target ?>"
				class="pagination-item default <?php if($item->disabled){ ?>disabled<?php } ?>"
				title="<?= $item->title ?>">
				<?= $item->target ?>
			</a>
			<?php
		}
	}
	?>
</div>
