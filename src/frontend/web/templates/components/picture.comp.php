<picture>
	<?php if($picture->show_source){ ?>
	<source srcset="<?= $picture->srcset ?>">
	<?php } ?>

	<img src="<?= $picture->original_src ?>" alt="<?= $picture->alt ?>">
</picture>
