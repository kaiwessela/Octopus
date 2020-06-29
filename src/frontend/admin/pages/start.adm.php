<h1>Admin-Bereich</h1>
<?php
if($not_found){
	?>
	<span class="message error">
		Die angefragte Seite (<?= $_GET['page'] ?>) wurde nicht gefunden.
	</span>
	<?php
}
?>
