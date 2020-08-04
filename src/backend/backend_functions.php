<?php
function generate_id() {
	return bin2hex(random_bytes(4));
}
?>
