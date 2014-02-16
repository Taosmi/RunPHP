<?php

namespace ProWeb\Extensions;
use ProWeb, ProWeb\Helpers;

/**
 * This class is a core extension. Implements the functionality to manage the 
 * input data that is received from the web. Inputs are parsed to prevent the 
 * most frequently and common security attacks. There are also validation 
 * options so data can be checked against some format or condition.
 * 
 * @author Miguel Angel Garcia
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
class Inputs extends ProWeb\Extension {

    // The data validation class name to be used.
    private static $DATAVAL_CLASS = 'ProWeb\Helpers\DataVal';
    // The list of allowed HTML tags.
    private static $TAGS_ALLOWED = '<a><b><br><img><p><ul><li>';


    /**
     * Initiates the extension. This extensions requires a Data Validation class
     * to be into the helpers folder, otherwise throws a system error exception.
     *
     * @throws \ProWeb\ErrorException  If the Data Validation helper could not be found.
     */
    public function init () {
        // This extension requires the DataVal helper class.
        if (!class_exists(self::$DATAVAL_CLASS)) {
            throw new ProWeb\ErrorException(1000, __('The Inputs extension requires a Data Validation helper class to be available.', 'Inputs'), array(
                'DataValHelper' => self::$DATAVAL_CLASS
            ), 'system');
        }
    }

    /**
     * Tests a key and his value against a filter validation. If the value does
     * not pass the validation, returns false. Otherwise returns true.
     *
     * @param string   $key     The key name that holds a request value.
     * @param string   $filter  A filter to apply or a function.
     * @param string   $param   A parameter used by the filter (optional).
     * @return boolean          True if the value pass the test. Otherwise false.
     * @throws \ProWeb\ErrorException  If the filter is not available.
     */
    public function check ($key, $filter, $param = null) {
        // Checks if the method exists.
        if (!method_exists(self::$DATAVAL_CLASS, $filter)) {
            throw new ProWeb\ErrorException(1001, __('The Inputs extension requires the filter to be available in the Data Validation helper class.', 'Inputs'), array(
                'filter' => $filter
            ), 'system');
        }
        // Gets the value and the function name.
        $value = $this->get($key);
        $filterFunction = self::$DATAVAL_CLASS.'::'.$filter;
        // Calls the function and returns the result.
        if ($param === null) {
            return call_user_func($filterFunction, $value);
        } else {
            return call_user_func($filterFunction, $value, $param);
        }
    }

    /**
     * Gets the value corresponding to the key from the input data. If the key 
     * does not exists, returns null.
     * 
     * @param string $key  The input data key name.
     * @return string      The corresponding value or null.
     */
    public function get ($key) {
        // If no input data, returns null.
        if (!array_key_exists($key, $this->controller->request['data'])) {
            return null;
        }
        // Parses the input data to avoid XSS attacks.
        return htmlentities(stripslashes(strip_tags($_REQUEST[$key], self::$TAGS_ALLOWED)), ENT_QUOTES);
    }
}