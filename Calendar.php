<?php
/**
 * OOP procedural consistent height calendar month generator in PHP.
 *
 * By Pim Schaaf | pimschaaf@gmail.com
 * https://github.com/pimschaaf/calendar
 *
 */

Class Calendar {
    private $now;
    private $options;
    private $margin;
    private $weekStartDay;
    private $dayFormat;
    private $monthFormat;
    private $yearFormat;
    private $calendar;
    private $callback;

    function __construct( $data, $margin = 6, $callback = false, $weekStartDay = 'Sunday', $dayFormat = '%d', $monthFormat = '%m', $yearFormat = '%Y', $locale = 'nl_NL' ) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' && $locale = 'nl_NL') {
            $locale = 'nld_NLD';
        }
        setlocale(LC_TIME, $locale);

        $this->now = $data['now'];

        //Set options
        if(isset($data['category'])) {
            $this->options['category'] = $data['category'];
        }

        if(isset($data['post_type'])){
            $this->options['post_type'] = $data['post_type'];
        }

        $this->margin = is_int($margin) ? $margin : 6;
        $this->callback = is_callable( (string) $callback) ? $callback : false;
        $this->weekStartDay = $weekStartDay;
        $this->dayFormat = $dayFormat;
        $this->monthFormat = $monthFormat;
        $this->yearFormat = $yearFormat;
        $this->calendar = $this->setCalendar();
    }

    function strftime($format, $unix_timestamp) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }
        return strftime($format, $unix_timestamp);
    }

    private function getStart() {
        if ($this->getMargin() !== 0) {
            $start = strtotime(sprintf('%d months', -1 * $this->getMargin(), $this->getNow()));
        } else {
            $start = $this->getNow();
        }

        return $this->getFirstDay($this->strftime('%m', $start), $this->strftime('%Y', $start));
    }

    private function getFirstDay($month, $year) {
        return mktime(0, 0, 0, $month, 1, $year) ;
    }

    private function getLastDay($month, $year) {
        return mktime(0, 0, 0, $month, $this->daysInMonth($month, $year), $year) ;
    }

    private function daysInMonth($month, $year) {
        return cal_days_in_month(0, $month, $year);
    }

    private function getPadding($month, $year) {
        return array('pre' => $this->getPrePadding($month, $year), 'post' => $this->getPostPadding($month, $year));
    }

    private function getPrePadding($month, $year) {
        $dayIndex = $this->getDayIndex();
        $prePadding = $dayIndex[$this->strftime('%a', $this->getFirstDay($month, $year))];
        return (int) $prePadding = $prePadding > 0 ? $prePadding : 7;
    }

    private function getPostPadding($month, $year) {
        $dayIndex = $this->getDayIndex($this->strftime('%a', $this->getLastDay($month, $year)));
        $minimalPadding = 7 - ($dayIndex +1 );
        $weeks = ($this->getPrePadding($month, $year) + $this->daysInMonth($month, $year) + $minimalPadding) / 7;
        return (int) $postPadding = $weeks > 5 ? $minimalPadding : $minimalPadding + 7;
    }

    public function getDayIndex($day = false) {
        $timestamp = strtotime('next ' . $this->weekStartDay);
        $days = array();
        for ($i = 0; $i < 7; $i++) {
            $days[$this->strftime('%a', $timestamp)] = $i;
            $timestamp = strtotime('+1 day', $timestamp);
        }

        if ($day) {
            return $days[$day];
        }
        return $days;
    }

    function getNow() {
        return $this->now;
    }

    function getMargin() {
        return $this->margin;
    }

    function setNow($now) {
        $this->now = $now;
    }

    function getYear($format = '%Y') {
        return $this->strftime($format, $this->now);
    }

    function getMonth($format = '%m') {
        return $this->strftime($format, $this->now);
    }

    function getDay($format = '%a') {
        return $this->strftime($format, $this->now);
    }

    function setDayFormat($format) {
        $this->dayFormat = $format;
    }

    function getDayFormat() {
        return $this->dayFormat;
    }

    function setMonthFormat($format) {
        $this->monthFormat = $format;
    }

    function getMonthFormat() {
        return $this->monthFormat;
    }

    function setYearFormat($format) {
        $this->yearFormat = $format;
    }

    function getYearFormat() {
        return $this->yearFormat;
    }

    function getOptions() {
        return $this->options;
    }

    /**
     * Procedural function to generate array which holds the calendar
     * @param string $dayFormat
     * @return array
     */
    private function setCalendar($dayFormat = '%d') {
        // set calendar start
        $start = $this->getStart();

        //for each year and month in margin, fill days
        $calendar = array();
        for ($i = 0; $i <= $this->margin * 2; $i++) {
            // Increase the month every loop
            $date = strtotime(sprintf('+%d months', $i), $start);

            // Set less verbose vars to work with
            $year = $this->strftime('%Y', $date);
            $prettyYear = $this->strftime($this->getYearFormat(), $date);
            $month = $this->strftime('%m', $date);
            $prettyMonth = $this->strftime($this->getMonthFormat(), $date);
            $daysInMonth = $this->daysInMonth($month, $year);
            $padding = $this->getPadding($month, $year);

            // for this month, loop through days
            // month will be padded with days from prev/next month
            // if first day of the month is the week start, the month will be pre-padded with an extra full week
            // in any case, the month will be post-padded to fill 6 weeks, i.e. 42 days
            for ($j = 1; $j <= $padding['pre'] + $daysInMonth + $padding['post']; $j++) {
                $day = $this->strftime($this->getDayFormat(), strtotime(sprintf('%d days', $j-$padding['pre']-1), $date));
                $loopDatetime = strtotime(sprintf('%d days', $j-$padding['pre']-1), $date);

                if ($this->callback) {
                    $calendar[$prettyYear][$prettyMonth][] = array('dayName' => $day, 'items' => call_user_func($this->callback, array($loopDatetime, $this->options)), 'blank' => $j <= $padding['pre'] || $j > $padding['pre'] + $daysInMonth);
                } else {
                    $calendar[$prettyYear][$prettyMonth][] = array('dayName' => $day, 'items' => array(), 'blank' => $j <= $padding['pre'] || $j > $padding['pre'] + $daysInMonth);
                }

            }
        }

        $this->calendar = $calendar;
        return $this->calendar;
    }

    function getCalendar($json = true) {
        return $calendar = $json ? json_encode($this->calendar) : $this->calendar;
    }
}
