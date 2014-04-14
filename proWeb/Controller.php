<?php

namespace proWeb;

/**
 * This class is an abstract class and must be extended to implement a Controller.
 * A controller runs when its path (relative to the webApp) matches the HTTP
 * request URL. The controller decides what to do next. By default, it provides
 * the functionality to manage the input data and to redirect to another Controller.
 *
 * @author Miguel Angel Garcia
 *
 * Copyright 2014 TAOSMI Technology
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
abstract class Controller {

    /**
     * The application configuration and the request info.
     */
    public $cfg, $request;

    // The data validation class name to be used.
    private static $DATAVAL_CLASS = 'proWeb\plugins\DataVal';

    // The list of allowed HTML tags.
    private static $TAGS_ALLOWED = '<a><b><br><img><p><ul><li>';


    /**
     * Abstract method to implement on any Controller. This method will be
     * executed by the framework just after the Controller is loaded.
     */
    abstract public function main ();


    /**
     * Loads the extensions involved. All the controllers get a reference to the
     * application configuration and to the request information.
     *
     * @param array $cfg      An application configuration.
     * @param array $request  The request information.
     */
    public function __construct ($cfg, $request) {
        $this->cfg = $cfg;
        $this->request = $request;
    }


    /**
     * Tests a key and his value against a filter validation. If the value does
     * not pass the validation, returns false. Otherwise returns true.
     *
     * @param string   $key     The key name that holds a request value.
     * @param string   $filter  A filter or function to apply.
     * @param string   $param   A parameter used by the filter (optional).
     * @return boolean          True if the value pass the test. Otherwise false.
     * @throws                  ErrorException if the filter is not available.
     */
    public function check ($key, $filter, $param = null) {
        // Checks if the method exists.
        if (!method_exists(self::$DATAVAL_CLASS, $filter)) {
            throw new ErrorException('PPW-010', __('The Data Validation class or the filter is not available.', 'system'), array(
                'DataVal' => self::$DATAVAL_CLASS,
                'filter' => $filter
            ), 'system');
        }
        // Gets the value and the function name.
        $value = $this->get($key);
        $filterFunction = self::$DATAVAL_CLASS.'::'.$filter;
        // Calls the function and returns the result.
        $result = call_user_func($filterFunction, $value, $param);
        if (!$result) {
            throw new ErrorException('PPW-011', __('Some parameter is missing or has a wrong value.', 'system'), array(
                'key' => $key,
                'value' => $value,
                'filter' => $filter,
                'param' => $param
            ));
        }
        return $value;
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
        if (!array_key_exists($key, $this->request['data'])) {
            return null;
        }
        // Parses the input data to avoid XSS attacks.
        return htmlentities(stripslashes(strip_tags($_REQUEST[$key], self::$TAGS_ALLOWED)), ENT_QUOTES);
    }

    /**
     * Redirects to another Controller. Must be used before sending or displaying
     * any data.
     *
     * @param string $to  The controller path.
     */
    public function redirect ($to) {
        // Updates the log.
        Logger::debug(__('Redirecting to Controller "%s".', 'system'), $to);
        Logger::flush($this->cfg);
        // Redirects the flow.
        header('Location: '.BASE_URL.$to);
        exit();
    }
}