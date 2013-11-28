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
class Input extends ProWeb\Extension {

    // The data validation class name to be used.
    private static $DATAVAL_CLASS = 'ProWeb\Helpers\DataVal';
    // The list of allowed HTML tags.
    private static $TAGS_ALLOWED = '<a><b><br><img><p><ul><li>';


    /**
     * Initiates the extension. This extensions requires the DataVal.php file 
     * to be into the helpers folder, otherwise throws an extension exception.
     * 
     * @throws  EXTException() if the DataVal helper could not be found.
     */
    public function init () {
        // This extension requires the DataVal helper class.
        if (!class_exists(self::$DATAVAL_CLASS)) {
            throw new ProWeb\ErrorException('1000');
        }
    }

    /*
     * Tests a key and his value against a filter validation. If the value does 
     * not pass the validation, throws an exception.
     * 
     * @param key     A string with the key name.
     * @param filter  A string with a filter to apply or a function.
     * @param param   A parameter used by the filter (optional).
     * @throws        ErrorException() if the filter does not exists.
     * @throws        ErrorException() if the key does not pass the validation.
     */
    public function check ($key, $filter, $param = null) {
        // Checks if the method exists.
        if (!method_exists(self::$DATAVAL_CLASS, $filter)) {
            throw new ProWeb\ErrorException('1001', array(
                'filter' => $filter
            ));
        }
        // Gets the value and the function name.
        $value = $this->get($key);
        $filterFunction = self::$DATAVAL_CLASS.'::'.$filter;
        // Calls the function and checks the result.
        if ($param === null) {
            $result = call_user_func($filterFunction, $value);
        } else {
            $result = call_user_func($filterFunction, $value, $param);
        }
        if (!$result) {
            throw new ProWeb\ErrorException('1002', array(
                'key' => $key,
                'value' => $value,
                'filter' => $filter,
                'param' => $param
            ));
        }
    }

    /**
     * Gets the value corresponding to the key from the input data. If the key 
     * does not exists, returns null.
     * 
     * @param key  A string with the input data key name.
     * @return     The corresponding value or null.
     */
    public function get ($key) {
        // If no input data, returns null.
        if (!array_key_exists($key, $this->controller->request['data'])) {
            return null;
        }
        // Parses the input data to avoid XSS attacks.
        return htmlentities(stripslashes(strip_tags($_REQUEST[$key], self::$TAGS_ALLOWED)),ENT_QUOTES);
    }
}
?>