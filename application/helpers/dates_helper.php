<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/16/2020
 * Time: 1:33 AM
 */

if(!function_exists('getMilliseconds')) {
    function getMilliseconds()
    {
        return str_replace(" ", "", str_replace(".", "", "" . microtime()));
    }
}

if(!function_exists('getUtcTimeBy')) {
    function getUtcTimeBy($diffDays = 0) {
        $dt = new DateTime();
        $dt->setTimeZone(new DateTimeZone('UTC'));

        try {
            $interval = new DateInterval("P{$diffDays}D");
            $dt->sub($interval);
        }
        catch(Exception $e) {}

        return $dt->format('Y-m-d\TH:i:s\Z');
    }
}

if(!function_exists('getDateTime')) {
    function getDateTime($format = '', $incHrs = 0, $time = null)
    {
        if ($format == null || strlen(trim($format)) == 0) {
            $format = "Y-m-d H:i:s";
        }

        if ($time == null) $time = time();

        $date = date($format, $time + $incHrs * 3600);
        return $date;
    }
}

if(!function_exists('getLastDateOfMonth')) {
    function getLastDateOfMonth($year, $month)
    {
        $query_date = "$year-$month-01";

        // Last day of the month.
        $lastDate = date('Y-m-t', strtotime($query_date));

        return $lastDate;
    }
}

if(!function_exists('getDateForWeekDayOfMonth')) {
    function getDateForWeekDayOfMonth($date, $weekDay, $weekOrd)
    {
        $Names = array(0 => "Sun", 1 => "Mon", 2 => "Tue", 3 => "Wed", 4 => "Thu", 5 => "Fri", 6 => "Sat");
        $ThisMonthTS = strtotime(date("Y-m-01", strtotime($date)));
        $NextMonthTS = strtotime(date("Y-m-01", strtotime("next month", strtotime($date))));

        $DateOfInterest = (-1 == $weekOrd) ?
            strtotime("last " . $Names[$weekDay], $NextMonthTS) : // The last occurrence of the day in this month.  Calculated as "last dayname" from the first of next month, which will be the last one in this month.
            strtotime($Names[$weekDay] . " + " . ($weekOrd - 1) . " weeks", $ThisMonthTS);

        return date('Y-m-d', $DateOfInterest);
    }
}

if(!function_exists('getStartAndEndDateOfWeek')) {
    function getStartAndEndDateOfWeek($week, $year, $dateFormat='Y-m-d')
    {
        $dateTime = new DateTime();
        $dateTime->setISODate($year, $week);
        $result['start_date'] = $dateTime->format($dateFormat);
        $dateTime->modify('+6 days');
        $result['end_date'] = $dateTime->format($dateFormat);
        return $result;
    }
}

if(!function_exists('time_diff')) {
    function time_diff(DateTimeInterface $b, DateTimeInterface $a, $absolute = false, $cap = 'H')
    {

        // Get unix timestamps, note getTimeStamp() is limited
        $b_raw = intval($b->format("U"));
        $a_raw = intval($a->format("U"));

        // Initial Interval properties
        $h = 0;
        $m = 0;
        $invert = 0;

        // Is interval negative?
        if (!$absolute && $b_raw < $a_raw) {
            $invert = 1;
        }

        // Working diff, reduced as larger time units are calculated
        $working = abs($b_raw - $a_raw);

        // If capped at hours, calc and remove hours, cap at minutes
        if ($cap == 'H') {
            $h = intval($working / 3600);
            $working -= $h * 3600;
            $cap = 'M';
        }

        // If capped at minutes, calc and remove minutes
        if ($cap == 'M') {
            $m = intval($working / 60);
            $working -= $m * 60;
        }

        // Seconds remain
        $s = $working;

        // Build interval and invert if necessary
        $interval = new DateInterval('PT' . $h . 'H' . $m . 'M' . $s . 'S');
        $interval->invert = $invert;

        return $interval;
    }
}

if(!function_exists('getDiffDays')) {
    function getDiffDays($date1, $date2)
    {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);

        $difference = $datetime1->diff($datetime2);

        return ($date2 < $date1 ? "-" : "") . "{$difference->days}";
    }
}

if(!function_exists('convertTimeZoneOfDate')) {
    function convertTimeZoneOfDate($date, $newTimeZone, $defaultTimeZone = '')
    {
        if (isEmptyString($defaultTimeZone)) {
            $defaultTimeZone = date_default_timezone_get();
        }
        $date = new DateTime($date, new DateTimeZone($defaultTimeZone));
        $date->setTimezone(new DateTimeZone($newTimeZone));

        return $date->format('Y-m-d');
    }
}