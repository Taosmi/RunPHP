<?php
/**
 * The Data Validation class is a static class that implements functionality to 
 * validate data. Add here your validation methods to use it later on the 
 * command controllers when validate your data with the Input->check method.
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
     * Checks if the value contains alphabetical characters (a-z) or 
     * digits (0-9) or dots (.) or commas (,) or dashes (-) or underscores (_) 
     * or semicolons (:).
     *
     * @param value  a string with the characters
     * @return       true if the value contains the characters, otherwise false
     */
    public static function alpha ($value) {
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^[\w\d-:\,\.]+$/')
        ));
    }

    /**
     * Checks if a value is a valid date. By default the date provided will 
     * be checked against the international pattern. Dashes (-) or slashes (/) 
     * or dots(.) may be used as separators. Available date patterns are:
     *      'eu'  => DD-MM-YYYY     Europe style
     *      'us'  => MM-DD-YYYY     USA style
     *      'int' => YYYY-MM-DD     International style
     *
     * @param value    a string with the date
     * @param pattern  a string with the date pattern name (optional)
     * @return         true if the value is a valid date, otherwise false
     */
    public static function date ($value, $pattern = 'int') {
        // Checks if the format is correct.
        $format = '/^(\d+)[-\.\/](\d+)[-\.\/](\d+)$/';
        $result = preg_match($format, $value, $date);
        if (!$result) {
            return false;
        }
        // Checks if the date is correct.
        array_shift($date);
        switch ($pattern) {
            case 'eu':
                list($day, $month, $year) = $date; break;
            case 'us':
                list($month, $day, $year) = $date; break;
            default:
                list($year, $month, $day) = $date;
        }
        return checkdate($month, $day, $year);
    }

    /**
     * Checks if a value contains only digits.
     *
     * @param value  a string with the digits
     * @return       true if the value contains only digits, otherwise false
     */
    public static function digits ($value) {
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => '/^\d+$/')
        ));
    }

    /**
     * Checks if a value is a valid email.
     *
     * @param value  a string with the email
     * @return       true if the value is a valid email, otherwise false
     */
    public static function email ($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Checks if a string has a specified length.
     *
     * @param value  a string
     * @param ln     a number that indicates the string length
     * @return       true if the string length is equal to the length provided, otherwise false
     */
    public static function length ($value, $ln) {
        return (strlen($value) === $ln);
    }

    /**
     * Checks if a string has a maximum length.
     *
     * @param value  a string
     * @param ln     a number that indicates the maximum string length
     * @return       true if the string length is smaller or equal than the length provided, otherwise false
     */
    public static function maxLength ($value, $maxLn) {
        return (strlen($value) <= $maxLn);
    }

    /**
     * Checks if a string has a minimum length.
     *
     * @param value  a string
     * @param ln     a number that indicates the minimum string length
     * @return       true if the string length is bigger or equal than the length provided, otherwise false
     */
    public static function minLength ($value, $minLn) {
        return (strlen($value) >= $minLn);
    }

    /**
     * Checks if a number has a valid format. By default the number provided 
     * will be checked against the German style. Available date patterns are:
     *      'ch' => 1'234'567'890,12    Swiss style
     *      'fr' => 1 234 567 890,12    French style
     *      'gb' => 1,234,567,890.12    British style
     *      'de' => 1.234.567.890,12    German style
     * 
     * @param value    a string with the number
     * @param pattern  a string with the pattern name (optional)
     * @return         true if the string is a valid number, otherwise false
     */
     public static function number ($value, $pattern = '') {
        // Gets the number format.
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
        // Checks if the format is correct.
        return filter_var($value, FILTER_VALIDATE_REGEXP, array(
            'options' => array('regexp' => $format)
        ));
    }

    /**
     * Checks if a value is in between of two values.
     *
     * @param value  a numeric value
     * @param range  a string with a minimum and maximum value separated by comma
     * @return       true if the value is in between, otherwise false
     */
    public static function range ($value, $range) {
        list($min, $max) = explode(',', $range);
        return (($value >= $min) && ($value <= $max));
    }

    /**
     * Checks if a value is not null and not an empty string.
     *
     * @param value  a string
     * @return       true if the characters is not null and not an empty string, otherwise false
     */
    public static function required ($value) {
        return ($value !== '' && $value !== null);
    }
}
?>