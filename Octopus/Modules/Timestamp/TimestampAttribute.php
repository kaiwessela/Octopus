<?php
namespace Octopus\Modules\Timestamp;
use Octopus\Core\Model\Attributes\StaticObjectAttribute;
use Octopus\Core\Model\Database\Condition;
use Octopus\Modules\Timestamp\Timestamp;
use Octopus\Modules\Timestamp\TimestampCompare;
use Octopus\Modules\Timestamp\TimestampRange;

class TimestampAttribute extends StaticObjectAttribute {
	protected bool $is_locked;


	final public function load(null|string|int|float $data) : void {
		if(is_null($data)){
			$this->value = null;
		} else {
			$this->value = new Timestamp($this);
			$this->value->from_db($data);
		}

		$this->is_loaded = true;
		$this->is_locked = true;
	}


	final protected function _edit(mixed $input) : void {
		if(is_null($input)){
			$this->value = null;
		} else {
			$this->value = new Timestamp($this);
			$this->value->from_form($input);

			if($this->value->is_null()){
				$this->value = null;
			}
		}
	}


	final public function get_push_value() : null|string|int|float {
		return $this->value?->to_db();
	}


	final public function arrayify() : null|string|int|float|bool|array {
		return $this->value?->to_w3c();
	}


	public function resolve_pull_condition(mixed $option) : ?Condition {
		if(is_string($option)){
			if(str_contains($option, '~')){
				$range = explode('~', $option, 2);

				$from = new Timestamp();
				$to = new Timestamp();

				$from->from_url($range[0]);
				$to->from_url($range[1]);

				return new TimestampRange($this, $from, $to);
			} else {
				$timestamp = new Timestamp();

				if(preg_match('/^([<>]=?)/', $option, $matches)){
					$timestamp->from_url(ltrim($option, '>=<'));

					return new TimestampCompare($this, $matches[1], $timestamp);
				} else {
					$timestamp->from_url($option);

					return new TimestampCompare($this, '=', $timestamp);
				}
			}
		} else {
			throw new Exception('Invalid condition.');
		}




		// TEMP
		if(is_string($option)){
			return new TimestampCompare($this, '=', $option);

			$this->value = new Timestamp();
			$this->value->from_url($value);
		} else {
			throw new Exception();
		}
	}

}
?>
