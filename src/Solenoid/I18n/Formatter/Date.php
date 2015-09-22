<?php
namespace Solenoid\I18n\Formatter;

use \Solenoid\I18n\I18n;
use \Solenoid\I18n\Locale;

class Date
{
    private static $_formatters = array(
        'G'=>'formatEra',
        'y'=>'formatYear',
        'M'=>'formatMonth',
        'L'=>'formatMonth',
        'd'=>'formatDay',
        'h'=>'formatHour12',
        'H'=>'formatHour24',
        'm'=>'formatMinutes',
        's'=>'formatSeconds',
        'E'=>'formatDayInWeek',
        'c'=>'formatDayInWeek',
        'e'=>'formatDayInWeek',
        'D'=>'formatDayInYear',
        'F'=>'formatDayInMonth',
        'w'=>'formatWeekInYear',
        'W'=>'formatWeekInMonth',
        'a'=>'formatPeriod',
        'k'=>'formatHourInDay',
        'K'=>'formatHourInPeriod',
        'z'=>'formatTimeZone',
        'Z'=>'formatTimeZone',
        'v'=>'formatTimeZone',
    );
    private static $_formats;

    public static function format($pattern, $value, $locale = null)
    {
        if (!$value instanceof \Solenoid\Date\Date) {
            $value = new \Solenoid\Date\Date($value);
        }

        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $tokens = self::_parseFormat($pattern);

        foreach ($tokens as &$token) {
            if (is_array($token)) { // a callback: method name, sub-pattern
                $token = self::$token[0]($token[1], $value, $locale);
            }
        }

        return implode('', $tokens);
    }

    public static function formatDate($value, $type = 'medium', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();

        return self::format($i18n->getDateFormat($type, $locale), $value,
            $locale);
    }

    public static function formatTime($value, $type = 'medium', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();

        return self::format($i18n->getTimeFormat($type, $locale), $value,
            $locale);
    }

    public static function formatDateTime($value, $dateType = 'medium',
        $timeType = 'medium', $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();

        if (isset($dateType) && !empty($dateType)) {
            $date = self::format($i18n->getDateFormat($dateType, $locale),
                $value, $locale);
        }

        if (isset($timeType) && !empty($timeType)) {
            $time = self::format($i18n->getTimeFormat($timeType, $locale),
                $value, $locale);
        }

        if (isset($date) && isset($time)) {
            $dateTimePattern = $i18n->getDateTimeFormat();
            return strtr($dateTimePattern, array('{0}'=>$time, '{1}'=>$date));
        } elseif (isset($date)) {
            return $date;
        } elseif (isset($time)) {
            return $time;
        }
    }

    protected static function formatYear($pattern, $date)
    {
        $year = $date->getYear();

        if ($pattern === 'yy') {
            return str_pad($year%100, 2, '0', STR_PAD_LEFT);
        } else {
            return str_pad($year, strlen($pattern), '0', STR_PAD_LEFT);
        }
    }

    protected static function formatMonth($pattern, $date, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();
        $month = $date->getMonth();

        switch ($pattern) {
            case 'M':
                return $month;
            case 'MM':
                return str_pad($month, 2, '0', STR_PAD_LEFT);
            case 'MMM':
                return $i18n->getMonthName($month, 'abbreviated', $locale);
            case 'MMMM':
                return $i18n->getMonthName($month, 'wide', $locale);
            case 'MMMMM':
                return $i18n->getMonthName($month, 'narrow', $locale);
            case 'L':
                return $month;
            case 'LL':
                return str_pad($month, 2, '0', STR_PAD_LEFT);
            case 'LLL':
                return $i18n->getMonthName($month, 'abbreviated', $locale,
                    true);
            case 'LLLL':
                return $i18n->getMonthName($month, 'wide', $locale);
            case 'LLLLL':
                return $i18n->getMonthName($month, 'narrow', $locale);
            default:
                throw new \RuntimeException('The pattern for month must be "M",'
                    .' "MM", "MMM", "MMMM", "L", "LL", "LLL" or "LLLL".');
        }
    }

    protected static function formatDay($pattern, $date)
    {
        $day = $date->getDay();

        if ($pattern === 'd') {
            return $day;
        } elseif ($pattern === 'dd') {
            return str_pad($day, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for day of the month must'
                .' be "d" or "dd".');
        }
    }

    protected static function formatDayInYear($pattern, $date)
    {
        $day = $date->getYearDay();

        if (($n = strlen($pattern)) <= 3) {
            return str_pad($day, $n, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for day in year must be'
                .' "D", "DD" or "DDD".');
        }
    }

    protected static function formatDayInMonth($pattern, $date)
    {
        if ($pattern === 'F') {
            return (int)(($date->getMonthDay() + 6) / 7);
        } else {
            throw new \RuntimeException('The pattern for day in month must be "F".');
        }
    }

    protected static function formatDayInWeek($pattern, $date, $locale = null)
    {
        if (!isset($locale)) {
            $locale = Locale::getLocale();
        }

        $i18n = new I18n();
        $day = $date->getWeekDay();

        switch ($pattern) {
            case 'E':
            case 'EE':
            case 'EEE':
            case 'eee':
                return $i18n->getWeekDayName($day, 'abbreviated', $locale);
            case 'EEEE':
            case 'eeee':
                return $i18n->getWeekDayName($day, 'wide', $locale);
            case 'EEEEE':
            case 'eeeee':
                return $i18n->getWeekDayName($day, 'narrow', $locale);
            case 'e':
            case 'ee':
            case 'c':
                return $day ? $day : 7;
            case 'ccc':
                return $i18n->getWeekDayName($day, 'abbreviated', $locale, true);
            case 'cccc':
                return $i18n->getWeekDayName($day, 'wide', $locale, true);
            case 'ccccc':
                return $i18n->getWeekDayName($day, 'narrow', $locale, true);
            default:
                throw new \RuntimeException('The pattern for day of the week'
                    .' must be "E", "EE", "EEE", "EEEE", "EEEEE", "e", "ee",'
                    .' "eee", "eeee", "eeeee", "c", "cccc" or "ccccc".');
        }
    }

    protected static function formatHour24($pattern, $date)
    {
        $hour = $date->getHours();

        if ($pattern === 'H') {
            return $hour;
        } elseif ($pattern === 'HH') {
            return str_pad($hour, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for 24 hour format must'
                .' be "H" or "HH".');
        }
    }

    protected static function formatHour12($pattern, $date)
    {
        $hour = $date->getHours();
        $hour = ($hour==12|$hour==0)?12:($hour)%12;

        if ($pattern === 'h') {
            return $hour;
        } elseif ($pattern === 'hh') {
            return str_pad($hour, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for 12 hour format must be'
                .' "h" or "hh".');
        }
    }

    protected static function formatHourInDay($pattern, $date)
    {
        $hour = $date->getHours() == 0?24:$date->getHours();

        if ($pattern === 'k') {
            return $hour;
        } elseif ($pattern === 'kk') {
            return str_pad($hour, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for hour in day must be'
                .' "k" or "kk".');
        }
    }

    protected static function formatHourInPeriod($pattern, $date)
    {
        $hour = $date->getHours()%12;

        if ($pattern === 'K') {
            return $hour;
        } elseif ($pattern === 'KK') {
            return str_pad($hour, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for hour in AM/PM must be'
                .' "K" or "KK".');
        }
    }

    protected static function formatMinutes($pattern, $date)
    {
        $minutes = $date->getMinutes();

        if ($pattern === 'm') {
            return $minutes;
        } elseif ($pattern === 'mm') {
            return str_pad($minutes, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for minutes must be "m"'
                .' or "mm".');
        }
    }

    protected static function formatSeconds($pattern, $date)
    {
        $seconds = $date->getSeconds();

        if ($pattern === 's') {
            return $seconds;
        } elseif ($pattern === 'ss') {
            return str_pad($seconds, 2, '0', STR_PAD_LEFT);
        } else {
            throw new \RuntimeException('The pattern for seconds must be'
                .' "s" or "ss".');
        }
    }

    protected static function formatTimeZone($pattern, $date)
    {
        if ($pattern[0] === 'z' || $pattern[0] === 'v') {
            return $date->format('T');
        } elseif ($pattern[0] === 'Z') {
            return $date->format('O');
        } else {
            throw new \RuntimeException('The pattern for time zone must be'
                .' "z" or "v".');
        }
    }

    private static function _parseFormat($pattern)
    {
        if (isset(self::$_formats[$pattern])) {
            return self::$_formats[$pattern];
        }

        $tokens = array();
        $n = strlen($pattern);
        $isLiteral = false;
        $literal = '';

        for ($i=0;$i<$n;++$i) {
            $c = $pattern[$i];

            if ($c==="'") {
                if ($i < $n-1 && $pattern[$i+1] === "'") {
                    $tokens[]="'";
                    $i++;
                } elseif ($isLiteral) {
                    $tokens[] = $literal;
                    $literal = '';
                    $isLiteral = false;
                } else {
                    $isLiteral = true;
                    $literal = '';
                }
            } elseif ($isLiteral) {
                $literal.=$c;
            } else {
                for ($j=$i+1; $j<$n; ++$j) {
                    if ($pattern[$j] !== $c) {
                        break;
                    }
                }

                $p = str_repeat($c, $j-$i);

                if (isset(self::$_formatters[$c])) {
                    $tokens[] = array(self::$_formatters[$c], $p);
                } else {
                    $tokens[] = $p;
                }

                $i = $j-1;
            }
        }

        if ($literal !== '') {
            $tokens[] = $literal;
        }

        return self::$_formats[$pattern] = $tokens;
    }
}
