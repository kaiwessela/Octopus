<div class="pagination">
	<?php
	$pagination->pages(function($page){
		if(!$page->is_first() && !$page->is_last()
			&& ($page->distance_to_current() > 3 && $page->distance_to_current() != 10)){

			return;
		}

		if($page->is_first()){
			$title = 'Erste Seite';
			$class = 'first-last';
		} else if($page->is_last()){
			$title = 'Letzte Seite';
			$class = 'first-last';
		} else if($page->is_current()){
			$title = 'Aktuelle Seite';
			$class = 'current';
		} else {
			$title = $page->number;
			$class = 'current';
		}

		?>
		<a href="<?= $page->href() ?>" class="pagination-item <?= $class ?>" title="<?= $title ?>">
			<?= $page->number ?>
		</a>
		<?php
	});
	?>
</div>
