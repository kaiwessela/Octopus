<ul>
	<li><a href="/admin" <?php if(is_null($AC->get_entity_name())){ ?>class="current" <?php } ?> >Startseite</a></li>
	<li>
		<div class="section-title">Inhalte</div>
		<ul>
			<?php foreach($AC->get_config() as $ename => $cfg){ ?>
				<li><a href="/admin/<?= $ename ?>" <?php if($ename === $AC->get_entity_name()){ ?>class="current" <?php } ?> >
					<?= $cfg['lang']['plural'] ?>
				</a></li>
			<?php } ?>
		</ul>
	</li>
</ul>
