<?php
/**
 * This class is a core extension. Implements the functionality to manage the 
 * input data that is received from the web. POST inputs are parsed to prevent 
 * the most frequently and common security attacks. There are also validation 
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
class eInputs {

    /**
     * Stores the parsed POST data.
     */
    private $inputs = array();

    /**
     * Stores the GET data entered after the URL path 'controller/action//'. 
     */
    private $queryString;

    /**
     * Parses POST data to avoid security issues, removing all html tags but a, 
     * b, br, img, p, ul and li, striping the slashes from the slashed quotes 
     * and encoding html entities and quotes (double and single).
     * 
     * @param key  a string with the key name
     * @return     the corresponding parsed value or null
     */
    private function parseInput ($key) {
        // If no POST data, returns null.
        if (!array_key_exists($key, $_POST)) {
            return null;
        }
        // The list of allowed HTML tags.
        $tags = '<a><b><br><img><p><ul><li>';
        // Parses the POST data to avoid XSS attacks.
        return htmlentities(stripslashes(strip_tags($_POST[$key], $tags)),ENT_QUOTES);
    }


    /**
     * Initiates the extension. This extensions requires the DataVal.php file 
     * to be into the helpers folder, otherwise throws an extension exception.
     * Remember that all the extensions will be initiated with a reference to 
     * the current command controller as unique parameter.
     * 
     * 
     * @param cmd  the command controller object reference
     * @throws     EXTException() if the DataVal helper could not be found
     */
    public function __construct (&$cmd) {
        // This extension requires the DataVal helper.
        if (!file_exists(HELPERS.'/DataVal.php')) {
            throw new EXTException('');
        }
        require_once(HELPERS.'/DataVal.php');
        // Loads the query string from the request.
        $this->queryString = $cmd->request->get('params');
    }

    /*
     * Checks a key value to pass a filter validation. If the value does not 
     * pass the validation, throws an extension exception.
     * 
     * @param key     a string with the key name
     * @param filter  a string with a filter to apply or a function
     * @param param   a parameter used by the filter (optional)
     * @throws        EXTException() if the filter does not exists
     * @throws        EXTException() if the key does not pass the validation
     */
    public function check ($key, $filter, $param = null) {
        // Gets the value and the filter function name.
        $value = $this->get($key);
        $filterFunction = 'DataVal::'.$filter;
        // Checks if the method exists.
        if (!method_exists('DataVal', $filter)) {
            throw new EXTException('', array(
                'filter' => $filter
            ));
        }
        // Calls the function and checks the result.
        if ($param === null) {
            $result = call_user_func($filterFunction, $value);
        } else {
            $result = call_user_func($filterFunction, $value, $param);
        }
        if (!$result) {
            throw new EXTException('', array(
                'key' => $key,
                'value' => $value,
                'filter' => $filter,
                'param' => $param
            ));
        }
    }

    /**
     * Gets the value corresponding to the key from the POST data. If the 
     * key does not exists, returns null.
     * 
     * @param key  a string with the POST data key name
     * @return     the corresponding value or null
     */
    public function get ($key) {
        // Gets the value from the Inputs array.
        if (!array_key_exists($key, $this->inputs)) {
            $this->inputs[$key] = $this->parseInput($key);
        }
        return $this->inputs[$key];
    }

    /**
     * Gets all the key-value pairs from the POST data.
     * 
     * @return  an associative array with all the key-value pairs
     */
    public function getAll () {
        foreach($_POST as $key => $value) {
            if (!array_key_exists($key, $this->inputs)) {
                $this->inputs[$key] = $this->parseInput($key);
            }
        }
        return $this->inputs;
    }

    /**
     * Gets the query string value.
     * 
     * @return  a string with the query string value
     */
    public function getQueryString () {
        return $this->queryString;
    }
}
?>