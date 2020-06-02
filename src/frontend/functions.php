<?php
# 9. Januar 2020
function to_date($timestamp) {
	return strftime('%e.&nbsp;%B&nbsp;%Y', $timestamp);
}

# 7.45 Uhr
function to_time($timestamp) {
	return strftime('%k.%M&nbsp;Uhr');
}

# Montag, 9. Januar 2020
function to_date_with_day($timestamp) {
	return strftime('%A,&nbsp;%e.&nbsp;%B&nbsp;%Y');
}

# Montag, den 9. Januar 2020
function to_date_with_day_sentence($timestamp) {
	return strftime('%A,&nbsp;den&nbsp;%e.&nbsp;%B&nbsp;%Y');
}

# 9. Januar 2020, 7.45 Uhr
function to_date_and_time($timestamp) {
	return strftime('%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr');
}

# Montag, 9. Januar 2020, 7.45 Uhr
function to_date_and_time_with_day($timestamp) {
	return strftime('%A,&nbsp;%e.&nbsp;%B&nbsp;%Y,&nbsp;%k.%M&nbsp;Uhr');
}

# Montag, den 9. Januar 2020 um 7.45 Uhr
function to_date_and_time_with_day_sentence($timestamp) {
	return strftime('%A,&nbsp;den&nbsp;%e.&nbsp;%B&nbsp;%Y&nbsp;um&nbsp;%k.%M&nbsp;Uhr');
}

# 2020-01-09T07:45
function to_html_time($timestamp) {
	return strtotime('Y-m-d\TH:i');
}
?>
