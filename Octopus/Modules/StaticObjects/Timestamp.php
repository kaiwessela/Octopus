<?php
namespace Octopus\Modules\StaticObjects;
use \Octopus\Core\Model\Attributes\StaticObject;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Config;
use \DateTime;
use \IntlDateFormatter;

class Timestamp extends StaticObject {
	# protected Entity $context;
	# protected AttributeDefinition $definition;
	protected DateTime $datetime;


	public function load(mixed $data) : void {
		$this->datetime = new DateTime($data);
	}


	public function export() : mixed {
		return $this->datetime->format('Y-m-d H:i:s');
	}


	public function arrayify() : mixed {
		return $this->datetime->format(DateTime::RFC2822);
	}


	function __toString() {
		return $this->arrayify();
	}


	public function edit(mixed $data) : void {
		$this->check_edit();

		if(!isset($this->datetime)){
			$this->datetime = new DateTime();
		}

		if(is_array($data)){
			if(isset($data['date'])){
				if(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $data['date'], $matches)){
					$this->datetime->setDate($matches[1], $matches[2], $matches[3]);
				} else {
					throw new IllegalValueException($this->definition, $data, 'date invalid');
				}
			} else {
				throw new IllegalValueException($this->definition, $data, 'date missing');
			}

			if(isset($data['time'])){
				if(preg_match('/^([0-9]{2}):([0-9]{2})$/', $data['time'], $matches)){
					$this->datetime->setTime($matches[1], $matches[2]);
				} else {
					throw new IllegalValueException($this->definition, $data, 'time invalid');
				}
			} else {
				$this->datetime->setTime(0, 0);
			}
		} else if(is_numeric($data)){
			$this->datetime->setTimestamp((int) $data);
		} else if(is_string($data)){
			if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}[T ][0-9]{2}:[0-9]{2}(:[0-9]{2})?$/', $data)){
				throw new IllegalValueException($this->definition, $data, 'invalid datetime format');
			}

			$this->datetime = new DateTime($data);
		} else {
			throw new IllegalValueException($this->definition, $data, 'invalid format');
		}
	}


	public function format(string $format) : string {
		$formatter = new IntlDateFormatter( // TEMP TESTING
			'de', // Config::get('Server.lang'),
			IntlDateFormatter::FULL,
			IntlDateFormatter::FULL,
			null,
			null,
			$format
		);

		return $formatter->format($this->datetime);

		// return $this->datetime->format($format);
	}


	public function to_unix() : int {
		return $this->datetime->getTimestamp();
	}


	public function to_w3c() : string {
		return $this->datetime->format(DateTime::W3C);
	}


	public function to_html_datetime() : string {
		return $this->datetime->format('Y-m-d H:i');
	}


	public function to_html_date() : string {
		return $this->datetime->format('Y-m-d');
	}


	public function to_html_time() : string {
		return $this->datetime->format('H:i');
	}


	public function is_now(string $accuracy = 'minute') : bool {
		$format = match($accuracy){};
	}


	public function is_future(string $accuracy = 'minute') : bool {

	}


	public function is_past(string $accuracy = 'minute') : bool {

	}
}
?>




<?php /*
namespace Blog\Model\DataTypes;
use \Blog\Model\Abstracts\DataType;
use \Blog\Model\Exceptions\IllegalValueException;

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
	const SECOND = '%S';


	function __construct($value) { // load data from database
		$this->db_datetime = $value;
		$this->unix = strtotime($value);
	}

	function __toString() { // "opposite" of load; export data to database format
		return $this->db_datetime;
	}

	public static function import(string $value) : Timestamp { // handle user input
		$regex = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}( [0-2][0-9]:[0-5][0-9](:[0-5][0-9])?)?$/';

		if(preg_match($regex, $value)){
			return new Timestamp($value);
		} else {
			throw new IllegalValueException(null, $value, $regex);
		}
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

	public function staticize() {
		return $this->iso();
	}

	public function now(string $accuracy = 'D') : bool {
		$format = match($accuracy){
			self::SECOND, 	's' => 'Y.m.d-H:i:s',
			self::MINUTE, 	'm' => 'Y.m.d-H:i',
			self::HOUR, 	'h' => 'Y.m.d-H',
			self::DAY, 		'D' => 'Y.m.d',
			self::MONTH, 	'M' => 'Y.m',
			self::YEAR, 	'Y' => 'Y',
			default => 'Y.m.d'
		};

		return date($format, $this->unix) === date($format, time());
	}

	public function future(string $accuracy = 'D') : bool {
		return !$this->now($accuracy) && ($this->unix > time());
	}

	public function past(string $accuracy = 'D') : bool {
		return !$this->now($accuracy) && ($this->unix < time());
	}

	public function today() : bool {
		return $this->now('D');
	}
}
*/
?>
