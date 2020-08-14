<?php
namespace Blog\Frontend\Web\Modules;

class TimeFormat {
	# 9. Januar 2020
	public function date($timestamp) {
		return strftime('%e.&nbsp;%B&nbsp;%Y', $timestamp);
	}

	# 7.45 Uhr
	public function time($timestamp) {
		return strftime('%k.%M&nbsp;Uhr', $timestamp);
	}

	# Montag, 9. Januar 2020
	public function date_with_day($timestamp) {
		return strftime('%A,&nbsp;%e.&nbsp;%B&nbsp;%Y', $timestamp);
	}

	# Montag, den 9. Januar 2020
	public function date_with_day_sentence($timestamp) {
		return strftime('%A,&nbsp;den&nbsp;%e.&nbsp;%B&nbsp;%Y', $timestamp);
	}

	# 9. Januar 2020, 7.45 Uhr
	public function date_and_time($timestamp) {
		return strftime('%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr', $timestamp);
	}

	# Montag, 9. Januar 2020, 7.45 Uhr
	public function date_and_time_with_day($timestamp) {
		return strftime('%A,&nbsp;%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr', $timestamp);
	}

	# Montag, den 9. Januar 2020 um 7.45 Uhr
	public function date_and_time_with_day_sentence($timestamp) {
		return strftime('%A,&nbsp;den&nbsp;%e.&nbsp;%B&nbsp;%Y&nbsp;um&nbsp;%k.%M&nbsp;Uhr', $timestamp);
	}

	# 2020-01-09T07:45
	public function html_time($timestamp) {
		return date('Y-m-d\TH:i', $timestamp);
	}
}
?>
