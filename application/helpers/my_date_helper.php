<?php
function add_date($givendate, $day = 0, $mth = 0, $yr = 0) {
	$cd = strtotime($givendate);
	$newdate = date('Y-m-d', mktime(date('h', $cd),
			date('i', $cd), date('s', $cd), date('m', $cd) + $mth,
			date('d', $cd) + $day, date('Y', $cd) + $yr));
	return $newdate;
}

function add_date_and_time($givendate, $day = 0, $mth = 0, $yr = 0) {
	$cd = strtotime($givendate);
	$newdate = date('Y-m-d h:i:s', mktime(date('h', $cd),
			date('i', $cd), date('s', $cd), date('m', $cd) + $mth,
			date('d', $cd) + $day, date('Y', $cd) + $yr));
	return $newdate;
}

function get_current_timestamp() {
	return date('Y-m-d H:i:s');
}

function format_date($the_date) {
	if ($the_date <> NULL && $the_date <> "")
		return date("Y-m-d H:i:00", strtotime($the_date));
	else
		return NULL;
}

function format_display_date($the_date) {
	if ($the_date <> NULL) {
		return date("m/d/Y", strtotime($the_date));
	} else {
		return "";
	}
}

function format_show_dates($start_date, $end_date) {
	if ($start_date <> NULL) {
		/*
		Figure out if we have one or two months
		If one month, show February 12-19.
		If two show Febrary 27-March 3
		Show the year based off the start date.
		*/
		$month = date('M', strtotime($start_date));
		$month2 = date('M', strtotime($end_date));
		if ($month == $month2) {
			$retVal = $month . ' ' . date('d', strtotime($start_date)) . ' - ' .
					date('d', strtotime($end_date)) . ', ' .
					date('Y', strtotime($start_date));
		} else {
			$retVal = $month . date('d', strtotime($start_date)) . ' - ' .
					$month2 . ' ' . date('d', strtotime($end_date)) . ', ' .
					date('Y', strtotime($start_date));
		}

		return $retVal;
	} else {
		return "";
	}
}
