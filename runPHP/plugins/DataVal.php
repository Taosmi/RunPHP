<?php

namespace runPHP\plugins;

/**
 * The Data Validation class is a static class that implements functionality to
 * validate data. Add here your validation methods to use it later on the
 * controllers when validate your data with the 'this->check' method.
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
class DataVal {

    /**
     * Check if a string only contains alphabetical characters (a-z) or digits
     * (0-9) or dots (.) or commas (,) or dashes (-) or underscores (_) or
     * semicolons (:).
     *
     * @param  string  $value  The string to validate.
     * @return boolean         True if the value contains the characters, otherwise false.
     */
    public static function alpha ($value) {
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^[\w\d-:\,\.]+$/')
        ));
    }

    /**
     * Check if a string contains a valid date. By default the date provided
     * will be checked against the international format. Dashes (-) or slashes
     * (/) or dots(.) may be used as separators. Available date formats are:
     *      'eu'  => DD-MM-YYYY     Europe format
     *      'us'  => MM-DD-YYYY     USA format
     *      'int' => YYYY-MM-DD     International format (default)
     *
     * @param  string  $value   A value with the date.
     * @param  string  $format  The input date format (optional).
     * @return boolean          True if the value is a valid date, otherwise false.
     */
    public static function date ($value, $format = 'int') {
        // Check if the format is correct.
        $pattern = '/^(\d+)[-\.\/](\d+)[-\.\/](\d+)$/';
        $result = preg_match($pattern, $value, $date);
        if (!$result) {
            return false;
        }
        // Check if the date is correct.
        switch ($format) {
            case 'eu':
                list(, $day, $month, $year) = $date; break;
            case 'us':
                list(, $month, $day, $year) = $date; break;
            default:
                list(, $year, $month, $day) = $date;
        }
        return checkdate($month, $day, $year);
    }

    /**
     * Check if a string contains only digits.
     *
     * @param  string  $value  The value with only digits.
     * @return boolean         True if the value contains only digits, otherwise false.
     */
    public static function digits ($value) {
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^\d+$/')
        ));
    }

    /**
     * Check if a string is a valid email.
     *
     * @param  string  $value  A value with an email.
     * @return boolean         True if the value is a valid email, otherwise false.
     */
    public static function email ($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Check if a string has a specific length.
     *
     * @param  string  $value   A string.
     * @param  int     $length  The supposed string length.
     * @return boolean          True if the string length is equal to the length provided, otherwise false.
     */
    public static function length ($value, $length) {
        return (strlen($value) === $length);
    }

    /**
     * Check if a string has a length smaller than the length provided.
     *
     * @param  string  $value      A string.
     * @param  int     $maxLength  The maximum string length.
     * @return boolean             True if the string length is smaller or equal than the length provided, otherwise false.
     */
    public static function maxLength ($value, $maxLength) {
        return (strlen($value) <= $maxLength);
    }

    /**
     * Check if a string has a length higher than the length provided.
     *
     * @param  string  $value      A string.
     * @param  int     $minLength  The minimum string length.
     * @return boolean             True if the string length is higher or equal than the length provided, otherwise false.
     */
    public static function minLength ($value, $minLength) {
        return (strlen($value) >= $minLength);
    }

    /**
     * Check if a number has a valid format. By default the number provided
     * will be checked against the German format. Available date formats are:
     *      'ch' => 1'234'567'890,12    Swiss format
     *      'fr' => 1 234 567 890,12    French format
     *      'gb' => 1,234,567,890.12    British format
     *      'de' => 1.234.567.890,12    German format (default)
     *
     * @param  string  $value    The number.
     * @param  string  $pattern  The number format (optional).
     * @return boolean           True if the string is a valid number, otherwise false.
     */
    public static function number ($value, $pattern = '') {
        // Get the number format.
        switch ($pattern) {
            case 'ch':
                $value = htmlspecialchars_decode($value, ENT_QUOTES);
                $format = '/^[+-]?\d{1,3}(?:\'?\d{3})*(,\d{1,2})?$/';
                break;
            case 'fr':
                $format = '/^[+-]?\d{1,3}(?: ?\d{3})*(,\d{1,2})?$/';
                break;
            case 'gb':
                $format = '/^[+-]?\d{1,3}(?:,?\d{3})*(\.\d{1,2})?$/';
                break;
            case 'de': default:
            $format = '/^[+-]?\d{1,3}(?:\.?\d{3})*(,\d{1,2})?$/';
        }
        // Check if the format is correct.
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => $format)
        ));
    }

    /**
     * Check if a value is in range.
     *
     * @param  int     $value  A value.
     * @param  string  $range  A minimum and maximum value separated by comma.
     * @return boolean         True if the value is in range, otherwise false.
     */
    public static function range ($value, $range) {
        list($min, $max) = explode(',', $range);
        return (($value >= $min) && ($value <= $max));
    }

    /**
     * Check if a value is not null and not an empty string.
     *
     * @param  string  $value  A value.
     * @return boolean         True if the value is not null and not empty, otherwise false.
     */
    public static function required ($value) {
        return ($value !== '' && $value !== null);
    }
}