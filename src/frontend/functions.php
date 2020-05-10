<?php
# 9. Januar 2020
function to_date($timestamp) {
	return strftime('%e. %B %Y', $timestamp);
}

# 7.45 Uhr
function to_time($timestamp) {
	return strftime('%k.%M Uhr');
}

# Montag, 9. Januar 2020
function to_date_with_day($timestamp) {
	return strftime('%A, %e. %B %Y');
}

# Montag, den 9. Januar 2020
function to_date_with_day_sentence($timestamp) {
	return strftime('%A, den %e. %B %Y');
}

# 9. Januar 2020, 7.45 Uhr
function to_date_and_time($timestamp) {
	return strftime('%e. %B %Y, %k.%M Uhr');
}

# Montag, 9. Januar 2020, 7.45 Uhr
function to_date_and_time_with_day($timestamp) {
	return strftime('%A, %e. %B %Y, %k.%M Uhr');
}

# Montag, den 9. Januar 2020 um 7.45 Uhr
function to_date_and_time_with_day_sentence($timestamp) {
	return strftime('%A, den %e. %B %Y um %k.%M Uhr');
}

# 2020-01-09T07:45
function to_html_time($timestamp) {
	return strtotime('Y-m-d\TH:i');
}
?>
