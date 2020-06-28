<?php
$pgi_num_calc_command = $pg_item[0];
$pgi_classes = $pg_item[1];
$pgi_title = $pg_item[2];
$pgi_text = $pg_item[3];

if($pgi_num_calc_command == 'first'){
	$pgi_number = 1;
} else if($pgi_num_calc_command == 'last'){
	$pgi_number = $pagination->page_count;
} else if($pgi_num_calc_command == 'current'){
	$pgi_number = $pagination->current_page;
} else {
	$pgi_number = 0;
	$match;
	preg_match('/^(\+|-){1}([0-9]+)$/', $pgi_num_calc_command, $match);

	if($match[1] == '+'){
		$pgi_number = $pagination->current_page + (int) $match[2];
	} else if($match[1] == '-'){
		$pgi_number = $pagination->current_page - (int) $match[2];
	}
}

$pgi_title = str_replace('{num}', $pgi_number, $pgi_title);
$pgi_text = str_replace('{num}', $pgi_number, $pgi_text);

if($pagination->page_exists($pgi_number)){
	$url = SERVER_URL . '/posts/' . $pgi_number;
	?>

	<a href="<?= $url ?>/" class="<?= $pgi_classes ?>" title="<?= $pgi_title ?>"><?= $pgi_text ?></a>

	<?php
} else {
	?>

	<div class="<?= $pgi_classes ?> hidden" title="<?= $pgi_title ?>"><?= $pgi_text ?></div>

	<?php
}
?>
