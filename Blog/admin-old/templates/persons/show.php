<section class="persons show">
	<table>
		<tr><td><em>ID:</em></td><td><?= $Object->id ?></td></tr>
		<tr><td><em>Long-ID:</em></td><td><?= $Object->longid ?></td></tr>
		<tr><td><em>Name:</em></td><td><?= $Object->name ?></td></tr>
		<tr>
			<td><em>Gruppenzugehörigkeiten:</em></td>
			<td>
			<?php $Object->grouprelations?->each(function($r) use ($server){ ?>
				<a href="<?= $server->url ?>/admin/groups/<?= $r->group->id ?>" class="button">
					<?= $r->group->name ?>
				</a>
			<?php }); ?>
			</td>
		</tr>
		<tr>
			<td><em>Profilbild:</em></td>
			<td>
				<?php if(!empty($Object->image)){ ?>
				<a href="<?= $server->url ?>/admin/images/<?= $Object->image->id ?>" class="button">
					<?= $Object->image->title ?? $Object->image->longid ?>
				</a>
				<img src="<?= $Object->image->src() ?>" alt="<?= $Object->image->alternative ?>">
				<?php } ?>
			</td>
		</tr>
	</table>
</section>
