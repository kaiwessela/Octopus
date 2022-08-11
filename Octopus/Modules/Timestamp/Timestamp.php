<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\StaticObject;
use \Octopus\Core\Model\Attributes\Exceptions\IllegalValueException;
use \Octopus\Core\Config;
use \DateTimeImmutable;
use \DateTimeZone;
use \IntlDateFormatter;
use \Exception;

class Timestamp extends StaticObject {
	# protected Entity $context;
	# protected AttributeDefinition $definition;
	protected DateTimeImmutable $datetime;


	public function load(mixed $data) : void {
		$this->datetime = new DateTimeImmutable($data, new DateTimeZone('UTC'));
	}


	public function export() : mixed {
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
	}


	public function arrayify() : mixed {
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format(DateTimeImmutable::RFC2822);
	}


	function __toString() {
		return $this->arrayify();
	}


	public static function parse_input(mixed $data, bool $round_up = false, bool $strict_format = true) : DateTimeImmutable {
		$timestring = null;
		$unix = null;
		if(is_array($data)){
			if(isset($data['date'])){
				$timestring = $data[$date];

				if(isset($data['time'])){
					$timestring .= ' '.$data['time'];

					if(isset($data['timezone'])){
						$timestring .= ' '.$data['timezone'];
					}
				}
			}
		} else if(is_string($data)){
			if(is_numeric($data)){
				$unix = (int) $data;
			} else {
				$timestring = $data;
			}
		} else if(is_int($data)){
			$unix = $data;
		} else {
			throw new Exception('invalid time format.');
		}

		$datetime = new DateTimeImmutable();

		if(isset($unix)){
			$datetime = $datetime->setTimestamp($unix);
		} else if(preg_match('/^(\d{4})-(\d{2})-(\d{2})([T ](\d{2}):(\d{2})(:(\d{2}))?)?([Z ](.+))?$/', $timestring, $matches)){
			$y = (int) $matches[1];
			$m = (int) $matches[2];
			$d = (int) $matches[3];
			$h = (int) (empty($matches[5]) ? (($round_up) ? 23 : 0) : $matches[5]);
			$i = (int) (empty($matches[6]) ? (($round_up) ? 59 : 0) : $matches[6]);
			$s = (int) (empty($matches[8]) ? (($round_up) ? 59 : 0) : $matches[8]);

			$timezone = $matches[10] ?? date_default_timezone_get();

			if(preg_match('/^ \d{2}:?\d{2}/', $timezone)){
				$timezone = '+'.ltrim($timezone, ' ');
			}

			$datetime = $datetime->setTimezone(new DateTimeZone($timezone));
			$datetime = $datetime->setDate($y, $m, $d);
			$datetime = $datetime->setTime($h, $i, $s);
		} else if(!$strict_format){
			$datetime = new DateTimeImmutable($timestring);
		} else {
			throw new Exception('invalid time format (not following strict format rules).');
		}

		return $datetime;
	}


	public function edit(mixed $data) : void {
		if(!isset($this->datetime)){
			$this->datetime = new DateTimeImmutable();
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
			if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}[T ][0-9]{2}:[0-9]{2}(:[0-9]{2})?( .+)?$/', $data)){
				throw new IllegalValueException($this->definition, $data, 'invalid datetime format');
			}

			$this->datetime = new DateTimeImmutable($data);
		} else {
			throw new IllegalValueException($this->definition, $data, 'invalid format');
		}
	}


	public function format(string $format, string $timezone = null) : string {
		$tz = new DateTimeZone($timezone ?? date_default_timezone_get());

		$formatter = new IntlDateFormatter( // TEMP TESTING
			'de', // Config::get('Server.lang'),
			IntlDateFormatter::FULL,
			IntlDateFormatter::FULL,
			$tz,
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
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format(DateTimeImmutable::W3C);
	}


	public function to_html_datetime() : string {
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i');
	}


	public function to_html_date() : string {
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d');
	}


	public function to_html_time() : string {
		return $this->datetime->setTimezone(new DateTimeZone('UTC'))->format('H:i');
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
