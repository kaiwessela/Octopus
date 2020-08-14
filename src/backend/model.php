<?php
namespace Blog\Backend;

interface Model {
	public function generate();
	public function pull($identifier);
	public function push();
	public function load($data);
	public function import($data);
}
?>
