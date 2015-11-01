<?php

namespace runPHP;
use runPHP\plugins\RepositoryPDO;

/**
 * This class provides the functionality to manage a REST HTTP request and its
 * four verbs: put, post, get and delete.
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
abstract class ApiController {

    /**
     * @var string  The data validation class name to be used.
     */
    private static $DATAVAL_CLASS = 'runPHP\plugins\DataVal';

    /**
     * @var string  The list of allowed HTML tags.
     */
    private static $TAGS_ALLOWED = '<a><b><br><img><p><ul><li>';

    /**
     * @var array  The request info.
     */
    public $request;


    /**
     * The controller get a reference to the request information.
     *
     * @param array  $request  The request information.
     */
    public function __construct ($request) {
        $this->request = $request;
    }

    /**
     * This function is derived on four other functions, each one match one
     * of the four HTTP verbs available and must be implemented by the developer.
     *
     * @return Response      A Response with the output data.
     * @throws RunException  If the HTTP verb is not available.
     */
    public function main () {
        switch ($this->request['method']) {
            case 'GET':
                return $this->get();
             case 'PUT':
                return $this->put();
             case 'POST':
                return $this->post();
             case 'DELETE':
                return $this->delete();
             default:
                throw new RunException(__('The HTTP verb used is not available.'), array(
                    'code' => 'RPP-020',
                    'helpLink' => 'http://runphp.taosmi.es/faq/rpp020'
                ), 500);
        }
    }

    /**
     * Requests with GET HTTP verb will run this method. Developer should
     * override this method with some logic. By default, this method sends a
     * HTTP error code 404 Page not found.
     *
     * @return Response  A Response with the output data.
     */
    public function get () {
        return new Response('data', null, 404);
    }

    /**
     * Requests with POST HTTP verb will run this method. Developer should
     * override this method with some logic. By default, this method sends a
     * HTTP error code 404 Page not found.
     *
     * @return Response  A Response with the output data.
     */
    public function post () {
        return new Response('data', null, 404);
    }

    /**
     * Requests with PUT HTTP verb will run this method. Developer should
     * override this method with some logic. By default, this method sends a
     * HTTP error code 404 Page not found.
     *
     * @return Response  A Response with the output data.
     */
    public function put () {
        return new Response('data', null, 404);
    }

    /**
     * Requests with DELETE HTTP verb will run this method. Developer should
     * override this method with some logic. By default, this method sends a
     * HTTP error code 404 Page not found.
     *
     * @return Response  A Response with the output data.
     */
    public function delete () {
        return new Response('data', null, 404);
    }


    /**
     * Test a key and his value against a filter validation. If the value does
     * not pass the validation, return an exception. Otherwise return the value
     * for the key provided.
     *
     * @param  string   $key     The key name that holds a request value.
     * @param  string   $filter  A filter or function to apply.
     * @param  string   $param   A parameter used by the filter (optional).
     * @return boolean           True if the value pass the test. Otherwise false.
     * @throws RunException      If the validation class or the filter is not available.
     * @throws RunException      If the value does not pass the validation.
     */
    public function inputCheck ($key, $filter, $param = null) {
        // Check if the method exists.
        if (!method_exists(self::$DATAVAL_CLASS, $filter)) {
            throw new RunException(__('The Data Validation class or the filter is not available.', 'system'), array(
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
            throw new RunException(sprintf(__('The parameter "%s" has a wrong value.', 'system'), $key), array(
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
     * Get the repository for the class specified.
     *
     * @param  string  $className  Class name of the repository objects.
     * @return RepositoryPDO       The object repository for the class name given.
     * @throws RunException        If fails.
     */
    public function repository ($className) {
        // Get the connection string.
        if (!isset($this->request['cfg']['REPOS']['connection'])) {
            throw new RunException(__('No connection string defined', 'system'), array(
                'code' => 'RPP-012',
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp012'
            ));
        }
        // Get the repository parameters from the app.cfg configuration.
        $connectString = $this->request['cfg']['REPOS']['connection'];
        $pks = $this->request['cfg']['REPOS'][$className];
        // Get the repository.
        if (class_exists($className.'Repository')) {
            // Get the specific repository.
            $repoClassName = $className.'Repository';
            $repo = new $repoClassName($connectString, $className, $pks);
        } else {
            // Get the generic repository.
            $repo = new RepositoryPDO($connectString, $className, $pks);
        }
        return $repo;
    }
}