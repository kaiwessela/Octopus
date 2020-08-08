<?php
class Picture {
	public $image;
	public $width;

	const PATH = $server->url . '/resources/images/dynamic/';
	const WIDTHS = [
		'extrasmall'	=> 	300,
		'small'			=> 	600,
		'middle'		=> 1200,
		'large'			=> 2400,
		'extralarge'	=> 4800
	];


	function __construct($image, $width = 300) {
		if(!$image instanceof Image){
			return false;
		}

		$this->image = $image;
		$this->width = $width;
	}

	public function display() {
		$height = round($this->width * (2 / 3));
		$path_base = self::PATH . $this->image->longid . '/';
		$orig_path = $path_base . 'original.' . $this->image->extension;
		$alt = $this->image->description;
		?>

		<picture>

			<?php
		$sources;
		foreach($this->image->sizes as $size){
			if($size == 'original'){
				continue;
			}

			$sources[] = $path_base . $size . '.' . $this->image->extension . ' '
				. self::WIDTHS[$size] . 'w';
			$w = self::WIDTHS[$size];
		}
			?>

			<source srcset="<?= implode(' ,', $sources); ?>">
			<img src="<?= $orig_path ?>" alt="<?= $alt ?>" width="<?= $this->width ?>"
				height="<?= $height ?>">
		</picture>

		<?php
	}
}
?>
