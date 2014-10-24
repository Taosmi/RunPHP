<?php

namespace runPHP;
use runPHP\plugins\RepositoryPDO;

/**
 * This class is an abstract class and must be extended to implement a
 * Controller. A controller runs when its path (relative to the webApp) matches
 * the HTTP request URL. The controller decides what to do next. By default, it
 * provides the functionality to manage input data, repository access and
 * redirect to another controller.
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
     * The data validation class name to be used.
     * @var string
     */
    private static $DATAVAL_CLASS = 'runPHP\plugins\DataVal';

    /**
     * The list of allowed HTML tags.
     * @var string
     */
    private static $TAGS_ALLOWED = '<a><b><br><img><p><ul><li>';

    /**
     * The request info.
     * @var array
     */
    public $request;

    /**
     * Abstract method to implement on any Controller. This method will be
     * executed by the framework just after the Controller is loaded.
     *
     * @return Response  A Response with the response data.
     */
    abstract public function main ();


    /**
     * The controller get a reference to the request information.
     *
     * @param array  $request  The request information.
     */
    public function __construct ($request) {
        $this->request = $request;
    }


    /**
     * Test a key and his value against a filter validation. If the value does
     * not pass the validation, returns false. Otherwise returns true.
     *
     * @param  string   $key     The key name that holds a request value.
     * @param  string   $filter  A filter or function to apply.
     * @param  string   $param   A parameter used by the filter (optional).
     * @return boolean           True if the value pass the test. Otherwise false.
     * @throws                   ErrorException if the validation class or the filter is not available.
     * @throws                   ErrorException if the value does not pass the validation.
     */
    public function inputCheck ($key, $filter, $param = null) {
        // Check if the method exists.
        if (!method_exists(self::$DATAVAL_CLASS, $filter)) {
            throw new ErrorException(__('The Data Validation class or the filter is not available.', 'system'), array(
                'code' => 'RPP-010',
                'dataValClass' => self::$DATAVAL_CLASS,
                'filter' => $filter,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp010'
            ));
        }
        // Get the value and the function name.
        $value = $this->inputGet($key);
        $filterFunction = self::$DATAVAL_CLASS.'::'.$filter;
        // Call the function and return the result.
        $result = call_user_func($filterFunction, $value, $param);
        if (!$result) {
            throw new ErrorException(sprintf(__('The parameter "%s" has a wrong value.', 'system'), $key), array(
                'code' => 'RPP-011',
                'parameter' => $key,
                'value' => $value,
                'filter' => $filter,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp011'
            ), 400);
        }
        return $value;
    }

    /**
     * Get the value corresponding to the key from the input data. If the key
     * does not exists, return null.
     *
     * @param  string  $key  The input data key name.
     * @return string        The corresponding value or null.
     */
    public function inputGet ($key) {
        // Parse the input data to avoid XSS attacks.
        if (array_key_exists($key, $_REQUEST)) {
            return htmlentities(stripslashes(strip_tags($_REQUEST[$key], self::$TAGS_ALLOWED)), ENT_QUOTES);
        }
        // If no input data, return null.
        return null;
    }

    /**
     * Redirect to another Controller. Must be used before sending or displaying
     * any data.
     *
     * @param string  $to  The controller path.
     */
    public function redirect ($to) {
        // Update the log.
        Logger::debug(__('Redirecting to Controller "%s".', 'system'), $to);
        Logger::flush($this->request['cfg']['LOGS']['path']);
        // Redirect the flow.
        header('Location: '.$to);
        exit();
    }

    /**
     * Get the repository for the object class specified.
     *
     * @param $className      The class name for the repository.
     * @return RepositoryPDO  The repository for the class.
     * @throws                ErrorException if errors.
     */
    public function repository ($className) {
        // Get the table.
        $table = substr($className, strrpos($className, '\\') + 1);
        // Get the connection string.
        if (!isset($this->request['cfg']['REPOS']['connection'])) {
            throw new ErrorException('No connection string defined');
        }
        $connectString = $this->request['cfg']['REPOS']['connection'];
        // Get the object primary key.
        if (!isset($this->request['cfg']['REPOS'][$className])) {
            throw new ErrorException('No pk defined for '.$className);
        }
        $pk = $this->request['cfg']['REPOS'][$className];
        // Get the repository.
        if (class_exists($className.'Repository')) {
            // Get the specific repository.
            $repoClassName = $className.'Repository';
            $repo = new $repoClassName($connectString);
        } else {
            // Get the generic repository.
            $repo = new RepositoryPDO($connectString);
        }
        // Configure the repository.
        $repo->from(strtolower($table));
        $repo->to($className, $pk);
        return $repo;
    }
}