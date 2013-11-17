<?php

namespace ProWeb\Helpers;


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
     * Converts a not international format date to an international format date.
     * By default the input date is assumed in European format date. If the 
     * input date does not match the format specified, does nothing. Available 
     * patterns are:
     *      'eu'  => DD-MM-YYYY     Europe style    (default)
     *      'us'  => MM-DD-YYYY     USA style
     * 
     * @param str      A string with a no international format date.
     * @param pattern  A string with the input date pattern name.
     * @return         A string with an international format date.
     */
    public static function daterize ($str, $pattern = 'eu') {
        // Checks if the format is correct.
        $format = '/^(\d+)([-\.\/])(\d+)[-\.\/](\d+)/';
        $result = preg_match($format, $str, $date);
        if (!$result) {
            return false;
        }
        // Checks and gets the date parts.
        switch ($pattern) {
            case 'us':
                list(, $month, $separator, $day, $year) = $date; break;
            case 'eu': default:
                list(, $day, $separator, $month, $year) = $date;
        }
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        // Returns the international formatted date.
        return $year.$separator.$month.$separator.$day;
    }

    /**
     * Formats a string with a number into a currency value. By default it will 
     * be formatted as German style currency. Available format patterns are:
     *      'ch' => 1'234'567'890,12    Swiss style
     *      'fr' => 1 234 567 890,12    French style
     *      'gb' => 1,234,567,890.12    British style
     *      'de' => 1.234.567.890,12    German style (default)
     * 
     * @param str      A string with a number.
     * @param pattern  A string with pattern name (optional).
     * @return         A string formatted as a currency value.
     */
    public static function monetize ($str, $pattern = 'de') {
        switch ($pattern) {
            case 'ch':
                return number_format($str, 2, ',', "'");
            case 'fr':
                return number_format($str, 2, ',', ' ');
            case 'gb':
                return number_format($str, 2, '.', ',');
            case 'de': default:
                return number_format($str, 2, ',', '.');
        }
    }

    /**
     * Truncates a string at the nearest white space found nearby the length 
     * provided and adds a final pad. The string will be truncated at 30 
     * characters length by default. If the string is shorter than the limit, 
     * does nothing.
     * 
     * @param str    A string to manipulate.
     * @param limit  The maximum length of the truncated string (optional).
     * @param pad    A string with a final pad (optional).
     * @return       A string truncated at the limit provided.
     */
    public static function truncate ($str, $limit = 30, $pad = '...') {
        // If the string is shorter, does nothing.
        if (strlen($str) <= $limit) {
            return $str;
        }
        // Truncates the string but not the words!
        $strTmp = substr($str, 0, $limit);
        $break = strrpos($strTmp, ' ');
        if ($break) {
            $strTmp = substr($strTmp, 0, $break);
        }
        return $strTmp.$pad;
    }

    /**
     * Converts an international format date to the format specified. By default 
     * the date will be converted to the European date format. If the input 
     * date is not an international format date, does nothing. 
     * Available patterns are:
     *      'eu'  => DD-MM-YYYY     Europe style    (default)
     *      'us'  => MM-DD-YYYY     USA style
     * 
     * @param str      A string with an international format date.
     * @param pattern  A string with the target date pattern name.
     * @return         A string with the date.
     */
    public static function undaterize ($str, $pattern = 'eu') {
        // Checks if the format is correct.
        $format = '/^(\d+)([-\.\/])(\d+)[-\.\/](\d+)/';
        $result = preg_match($format, $str, $date);
        if (!$result) {
            return $str;
        }
        // Gets the international date parts.
        list(, $year, $separator, $month, $day) = $date;
        // Returns the date with the new format.
        switch ($pattern) {
            case 'us':
                return $month.$separator.$day.$separator.$year;
            case 'eu': default:
                return $day.$separator.$month.$separator.$year;
        }
    }

    /**
     * Makes the first letter of any word in capital, even if they are
     * separated by dot.
     *
     * @param str  A string to manipulate.
     * @return     A string with the first letter of all words in upper case.
     */
    public static function wordUC ($str) {
        $str = strtolower($str);
        return str_replace('. ','.',ucwords(str_replace('.','. ',$str)));
    }
}
?>