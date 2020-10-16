<?php
namespace Blog\Frontend\Web\Modules;

class Timestamp {
	public $unix;

	const DATE_SHORT = '%d.%m.%Y'; # 09.01.2020
	const DATE = '%e.&nbsp;%B&nbsp;%Y'; # 9. Januar 2020
	const DATE_LONG = '%A,&nbsp;%e.&nbsp;%B&nbsp;%Y'; # Montag, 9. Januar 2020
	const DATETIME_SHORT = '%d.%m.%Y,&nbsp;%k.%M&nbsp;Uhr'; # 09.01.2020, 7.45 Uhr
	const DATETIME = '%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr'; # 9. Januar 2020, 7.45 Uhr
	const DATETIME_LONG = '%A,&nbsp;%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr'; # Montag, 9. Januar 2020, 7.45 Uhr
	const TIME = '%k.%M&nbsp;Uhr'; # 7.45 Uhr


	function __construct(int $unix) {
		$this->unix = $unix;
	}

	function __toString() {
		return $this->unix;
	}

	public function unix() {
		return $this->unix;
	}

	public function iso() {
		return date('c', $this->unix);
	}

	public function rfc2822() {
		return date('r', $this->unix);
	}

	public function format($pattern = self::DATETIME) {
		return strftime($pattern, $this->unix);
	}

	public static function now() {
		return new Timestamp(time());
	}

	public function date_short() {
		return $this->format(Timestamp::DATE_SHORT);
	}

	public function date() {
		return $this->format(Timestamp::DATE);
	}

	public function date_long() {
		return $this->format(Timestamp::DATE_LONG);
	}

	public function datetime_short() {
		return $this->format(Timestamp::DATETIME_SHORT);
	}

	public function datetime() {
		return $this->format(Timestamp::DATETIME);
	}

	public function datetime_long() {
		return $this->format(Timestamp::DATETIME_LONG);
	}

	public function time() {
		return $this->format(Timestamp::TIME);
	}
}
?>
