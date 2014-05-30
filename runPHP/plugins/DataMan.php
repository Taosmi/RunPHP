<?php

namespace runPHP\plugins;

/**
 * The Data Manipulation class is a static class that implements functionality
 * to manipulate data.
 *
 * @author Miguel Angel Garcia Reguera
 *
 * Copyright 2012 TAOSMI Technology
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class DataMan {

    /**
     * Convert a not international format date to an international format date.
     * By default the input date is assumed in European format. If the input
     * date does not match the format specified, do nothing. Available input
     * formats are:
     *      'eu' => DD-MM-YYYY     Europe format (default)
     *      'us' => MM-DD-YYYY     USA format
     *
     * @param  string  $value   A no international format date.
     * @param  string  $format  The input date format.
     * @return string           An international format date.
     */
    public static function daterize ($value, $format = 'eu') {
        // Check the value format.
        $pattern = '/^(\d+)([-\.\/])(\d+)[-\.\/](\d+)/';
        $result = preg_match($pattern, $value, $date);
        if (!$result) {
            return false;
        }
        // Check and get the date parts.
        switch ($format) {
            case 'us':
                list(, $month, $separator, $day, $year) = $date; break;
            case 'eu': default:
            list(, $day, $separator, $month, $year) = $date;
        }
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        // Return the international formatted date.
        return $year.$separator.$month.$separator.$day;
    }

    /**
     * Format a string with a number into a currency value. By default it will
     * be formatted as German currency. Available formats are:
     *      'ch' => 1'234'567'890,12    Swiss format
     *      'fr' => 1 234 567 890,12    French format
     *      'gb' => 1,234,567,890.12    British format
     *      'de' => 1.234.567.890,12    German format (default)
     *
     * @param  string  $value   A number.
     * @param  string  $format  The currency format (optional).
     * @return string           The value formatted as a currency value.
     */
    public static function monetize ($value, $format = 'de') {
        switch ($format) {
            case 'ch':
                return number_format($value, 2, ',', "'");
            case 'fr':
                return number_format($value, 2, ',', ' ');
            case 'gb':
                return number_format($value, 2, '.', ',');
            case 'de': default:
            return number_format($value, 2, ',', '.');
        }
    }

    /**
     * Truncate a string at the nearest white space found nearby the length
     * provided and add a final pad. The string will be truncated at 30
     * characters length by default. If the string is shorter than the limit,
     * do nothing.
     *
     * @param  string  $string  A string to manipulate.
     * @param  int     $limit   The maximum length of the output string (optional).
     * @param  string  $pad     The final pad (optional).
     * @return string           A string truncated at the limit provided.
     */
    public static function truncate ($string, $limit = 30, $pad = '...') {
        // If the string is shorter, do nothing.
        if (strlen($string) <= $limit) {
            return $string;
        }
        // Truncate the string but not the words!
        $strTmp = substr($string, 0, $limit);
        $break = strrpos($strTmp, ' ');
        if ($break) {
            $strTmp = substr($strTmp, 0, $break);
        }
        return $strTmp.$pad;
    }

    /**
     * Convert an international format date to another date format. By default
     * the date will be converted to the European format. If the input date is
     * not an international format date, do nothing.
     * Available formats are:
     *      'eu'  => DD-MM-YYYY     Europe format    (default)
     *      'us'  => MM-DD-YYYY     USA format
     *
     * @param  string  $iDate   An international format date.
     * @param  string  $format  The output date format.
     * @return string           A string with the date.
     */
    public static function undaterize ($iDate, $format = 'eu') {
        // Check if the format is correct.
        $pattern = '/^(\d+)([-\.\/])(\d+)[-\.\/](\d+)/';
        $result = preg_match($pattern, $iDate, $date);
        if (!$result) {
            return $iDate;
        }
        // Get the international date parts.
        list(, $year, $separator, $month, $day) = $date;
        // Returns the date with the new format.
        switch ($format) {
            case 'us':
                return $month.$separator.$day.$separator.$year;
            case 'eu': default:
            return $day.$separator.$month.$separator.$year;
        }
    }

    /**
     * Make the first letter of any word in capital, even if they are separated
     * by dot.
     *
     * @param  string  $value  A string to manipulate.
     * @return string          A string with the first letter of all words in upper case.
     */
    public static function wordUC ($value) {
        $str = strtolower($value);
        return str_replace('. ','.',ucwords(str_replace('.','. ',$str)));
    }
}