<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\StaticObject;
use \Octopus\Modules\Timestamp\TimestampAttribute;
use \DateTimeImmutable;
use \DateTimeZone;
use \DateInterval;
use \IntlDateFormatter;
use \Exception;

class Timestamp extends StaticObject {
	protected ?TimestampAttribute $attribute;
	protected ?DateTimeImmutable $datetime;
	protected int $granularity;


	function __construct(?TimestampAttribute $attribute = null) {
		$this->attribute = &$attribute;
		$this->datetime = null;
		$this->granularity = 1;
	}


	// public static function new(string $datetime = 'now', ?DateTimeZone $timezone = null) : Timestamp {
	// 	return new static(new DateTimeImmutable($datetime, $timezone));
	// }


	public function now() : void {
		$this->require_editable();

		$timezone = new DateTimeZone(date_default_timezone_get());
		$this->datetime = new DateTimeImmutable('now', $timezone);
	}


	public function from_db(string $data) : void {
		$this->require_editable();

		$this->datetime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data, new DateTimeZone('UTC'));

		if($this->datetime === false){
			throw new Exception("Invalid data: «{$data}».");
		}
	}


	public function from_form(mixed $data) : void {
		$this->require_editable();

		if(is_int($data) || is_numeric($data)){
			$unix = (int) $data;

			$datetime = DateTimeImmutable::setTimestamp($unix);
			$this->granularity = 1;
		} else if(is_string($data)){
			// TODO

			// TEMP
			// $datetime = DateTimeImmutable::createFromFormat(DateTimeImmutable::W3C, $data);
			$this->from_url($data);
			return;

		} else if(is_array($data)){
			if(empty($data['date']) || empty($data['time'])){
				$this->datetime = null;
				return;
			}

			if(empty($data['timezone'])){
				$timezone = new DateTimeZone(date_default_timezone_get());
			} else {
				$timezone = new DateTimeZone($data['timezone']);
			}

			if($timezone === false){
				throw new Exception('Invalid timezone.');
			}

			$datetimestring = $data['date'].' '.$data['time'];

			// $datetime = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $datetimestring, $timezone);
			$datetime = new DateTimeImmutable($datetimestring, $timezone); // TEMP
		}

		$this->granularity = 1; // TEMP

		if($datetime === false){
			throw new Exception('Invalid date or time data.');
		} else {
			$this->datetime = $datetime;
		}

	}


	public function from_url(string $data) : void { // accepts w3c and iso-8601
		$this->require_editable();

		if(!preg_match('/^(\d{4})(-(\d{2})(-(\d{2})(T(\d{2})(:(\d{2})(:(\d{2}))?)?)?)?)?(Z|[ +-]\d{2}:?\d{2})?$/', $data, $matches)){
			throw new Exception('Invalid format.');
		}

		$y = (int) $matches[1];
		$m = 1;
		$d = 1;
		$h = 0;
		$i = 0;
		$s = 0;
		$timezone = date_default_timezone_get();
		$granularity = static::YEAR;

		if(!empty($matches[3])){
			$m = (int) $matches[3];
			$granularity = static::MONTH;
		}

		if(!empty($matches[5])){
			$d = (int) $matches[5];
			$granularity = static::DAY;
		}

		if(!empty($matches[7])){
			$h = (int) $matches[7];
			$granularity = static::HOUR;
		}

		if(!empty($matches[9])){
			$i = (int) $matches[9];
			$granularity = static::MINUTE;
		}

		if(!empty($matches[11])){
			$s = (int) $matches[11];
			$granularity = static::SECOND;
		}

		if(!empty($matches[12])){
			$timezone = str_replace(' ', '+', $matches[12]);
		}

		$dtz = new DateTimeZone($timezone);
		if($dtz === false){
			throw new Exception('Invalid timezone.');
		}

		$datetime = new DateTimeImmutable('now', $dtz);

		$datetime = $datetime->setDate($y, $m, $d);
		if($datetime === false){
			throw new Exception('Invalid date.');
		}

		$datetime = $datetime->setTime($h, $i, $s);
		if($datetime === false){
			throw new Exception('Invalid time.');
		}

		$this->datetime = $datetime;
		$this->granularity = $granularity;
	}


	public function ceil(?int $granularity = null) : void {
		$this->require_editable();

		if($granularity === null){
			$granularity = $this->granularity;
		}

		$s = (int) $this->datetime->format('s');
		$i = (int) $this->datetime->format('i');
		$h = (int) $this->datetime->format('H');
		$d = (int) $this->datetime->format('d');
		$m = (int) $this->datetime->format('m');
		$y = (int) $this->datetime->format('Y');

		if($granularity >= static::MINUTE){
			$s = 59;
		}

		if($granularity >= static::HOUR){
			$i = 59;
		}

		if($granularity >= static::DAY){
			$h = 23;
		}

		if($granularity >= static::MONTH){
			$d = (int) $this->datetime->format('t');
		}

		if($granularity >= static::YEAR){
			$m = 12;
			$d = 31;
		}

		$this->datetime = $this->datetime->setDate($y, $m, $d);
		$this->datetime = $this->datetime->setTime($h, $i, $s);
	}


	public function floor(?int $granularity = null) : void {
		$this->require_editable();

		if($granularity === null){
			$granularity = $this->granularity;
		}

		$s = (int) $this->datetime->format('s');
		$i = (int) $this->datetime->format('i');
		$h = (int) $this->datetime->format('H');
		$d = (int) $this->datetime->format('d');
		$m = (int) $this->datetime->format('m');
		$y = (int) $this->datetime->format('Y');

		if($granularity >= static::MINUTE){
			$s = 0;
		}

		if($granularity >= static::HOUR){
			$i = 0;
		}

		if($granularity >= static::DAY){
			$h = 0;
		}

		if($granularity >= static::MONTH){
			$d = 1;
		}

		if($granularity >= static::YEAR){
			$m = 1;
		}

		$this->datetime = $this->datetime->setDate($y, $m, $d);
		$this->datetime = $this->datetime->setTime($h, $i, $s);
	}


	public function convert_timezone(string $timezone) : void {
		$dtz = new DateTimeZone($timezone);
		if($dtz === false){
			throw new Exception('Invalid timezone.');
		}

		$this->datetime = $this->datetime->setTimezone($dtz);
	}


	public function to_db() : string {
		return $this->datetime->setTimezone(new DateTimeZone('utc'))->format('Y-m-d H:i:s');
	}


	public function to_unix() : int {
		return $this->datetime->getTimestamp();
	}


	public function to_w3c() : string {
		return $this->datetime->format(DateTimeImmutable::W3C);
	}

	public function to_short_w3c(string|int $granularity = 1){
		$granularity = match($granularity){
			'second' => static::SECOND,
			'minute' => static::MINUTE,
			'hour' => static::HOUR,
			'day' => static::DAY,
			'month' => static::MONTH,
			'year' => static::YEAR,
			default => $granularity
		};

		return $this->datetime->format(match($granularity){
			static::SECOND 	=> 'Y-m-d\TH:i:sP',
			static::MINUTE 	=> 'Y-m-d\TH:iP',
			static::HOUR 	=> 'Y-m-d\THP',
			static::DAY 	=> 'Y-m-dP',
			static::MONTH 	=> 'Y-mP',
			static::YEAR 	=> 'YP',
			default 		=> throw new Exception('Invalid granularity.')
		});
	}


	public function to_iso8601() : string {
		return $this->datetime->format(DateTimeImmutable::ISO8601);
	}


	public function to_rfc2822() : string {
		return $this->datetime->format(DateTimeImmutable::RFC2822);
	}


	public function to_html_date() : string {
		return $this->datetime->format('Y-m-d');
	}


	public function to_html_time() : string {
		return $this->datetime->format('H:i');
	}


	public function format(string $format) : string { // TEMP
		$formatter = new IntlDateFormatter(
			'de',
			IntlDateFormatter::FULL,
			IntlDateFormatter::FULL,
			$this->datetime->getTimezone(),
			null,
			$format
		);

		return $formatter->format($this->datetime);
	}


	public function diff(Timestamp $timestamp) : DateInterval { // TEMP
		return $this->datetime->diff($timestamp->datetime);
	}


	public function is_null() : bool {
		return $this->datetime === null;
	}


	public function equals(mixed $object) : bool {
		if(!$object instanceof Timestamp){
			return false;
		}

		return $this->datetime->setTimezone(new DateTimeZone('UTC')) === $object->setTimezone(new DateTimeZone('UTC'));
	}


	public function is_editable() : bool {
		return !isset($this->attribute) || $this->is_null();
	}


	protected function require_editable() : void {
		if(!$this->is_editable()){
			throw new Exception('not allowed because not independent'); // TODO
		}
	}


	public function get_granularity() : int {
		return $this->granularity;
	}


	final const SECOND 	= 1;
	final const MINUTE 	= 60;
	final const HOUR 	= 3600;
	final const DAY 	= 24 * 3600;
	final const MONTH 	= 30 * 24 * 3600;
	final const YEAR 	= 12 * 30 * 24 * 3600;
}
?>
