<div class="pagination">
	<?php
	foreach($pagination->items as $page){
		if(!$page->is_first() && !$page->is_last()
			&& ($page->difference_to_current() > 3 && $page->difference_to_current() != 10)){

			continue;
		}

		if($page->is_current()){
			$title = 'Aktuelle Seite';
			$class = 'current';
		} else if($page->is_first()){
			$title = 'Erste Seite';
			$class = 'first-last';
		} else if($page->is_last()){
			$title = 'Letzte Seite';
			$class = 'first-last';
		} else {
			$title = $page->number;
			$class = 'current';
		}

		?>
		<a href="<?= $page->href() ?>" class="pagination-item <?= $class ?>" title="<?= $title ?>">
			<?= $page->number ?>
		</a>
		<?php
	}
	?>
</div>
