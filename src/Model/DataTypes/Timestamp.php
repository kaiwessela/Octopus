<?php
namespace Blog\Model\DataTypes;
use \Blog\Model\Abstracts\DataType;

class Timestamp implements DataType {
	private string $db_datetime;
	private int $unix;

	const DATE_SHORT = '%d.%m.%Y'; # 09.01.2020
	const DATE = '%e.&nbsp;%B&nbsp;%Y'; # 9. Januar 2020
	const DATE_LONG = '%A,&nbsp;%e.&nbsp;%B&nbsp;%Y'; # Montag, 9. Januar 2020
	const DATETIME_SHORT = '%d.%m.%Y,&nbsp;%k.%M&nbsp;Uhr'; # 09.01.2020, 7.45 Uhr
	const DATETIME = '%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr'; # 9. Januar 2020, 7.45 Uhr
	const DATETIME_LONG = '%A,&nbsp;%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr'; # Montag, 9. Januar 2020, 7.45 Uhr
	const TIME = '%k.%M&nbsp;Uhr'; # 7.45 Uhr

	const WEEKDAY = '%A';
	const DAY = '%d';
	const MONTH = '%e';
	const MONTHNAME = '%B';
	const YEAR = '%Y';
	const HOUR = '%k';
	const MINUTE = '%M';


	function __construct($value) { // load data from database
		$this->db_datetime = $value;
		$this->unix = strtotime($value);
	}

	function __toString() { // "opposite" of load; export data to database format
		return $this->db_datetime;
	}

	function import($value) { // handle user input

	}

	public function unix() : int {
		return $this->unix;
	}

	public function iso() : string {
		return date('c', $this->unix);
	}

	public function rfc2822() : string {
		return date('r', $this->unix);
	}

	public function date(string $format) : string {
		return date($format, $this->unix);
	}

	public function strftime(string $format) : string {
		return strftime($format, $this->unix);
	}

	public function format(string $format = self::DATETIME) {
		return $this->strftime(match($format){
			'date_short' 		=> self::DATE_SHORT,
			'date' 				=> self::DATE,
			'date_long' 		=> self::DATE_LONG,
			'datetime_short' 	=> self::DATETIME_SHORT,
			'datetime' 			=> self::DATETIME,
			'datetime_long' 	=> self::DATETIME_LONG,
			'time' 				=> self::TIME,
			'weekday' 			=> self::WEEKDAY,
			'day' 				=> self::DAY,
			'month' 			=> self::MONTH,
			'monthname' 		=> self::MONTHNAME,
			'year' 				=> self::YEAR,
			'hour' 				=> self::HOUR,
			'minute' 			=> self::MINUTE,
			default 			=> $format
		});
	}
}
?>
