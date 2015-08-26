<?php

namespace runPHP\plugins;
use runPHP\RunException, runPHP\Response;

/**
 * This class extends the Controller class. It provides the functionality to
 * manage a REST HTTP request and its four verbs: put, post, get and delete.
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
abstract class ApiController extends Controller {

    /**
     * Requests with GET HTTP verb will run this function.
     *
     * @return Response  A Response with the output data.
     */
    abstract public function get();

    /**
     * Requests with POST HTTP verb will run this function.
     *
     * @return Response  A Response with the output data.
     */
    abstract public function post();

    /**
     * Requests with PUT HTTP verb will run this function.
     *
     * @return Response  A Response with the output data.
     */
    abstract public function put();

    /**
     * Requests with DELETE HTTP verb will run this function.
     *
     * @return Response  A Response with the output data.
     */
    abstract public function delete();


    /**
     * This function is derived on four other functions, each one match one
     * of the four HTTP verbs available and must be implemented by the developer.
     *
     * @return Response  A Response with the output data.
     * @throws           RunException when the HTTP method does not match.
     */
    public function main () {
        if ($this->request['method'] === 'GET') {
            return $this->get();
        } else if ($this->request['method'] === 'POST') {
            return $this->post();
        } else if ($this->request['method'] === 'PUT') {
            return $this->put();
        } else if ($this->request['method'] === 'DELETE') {
            return $this->delete();
        } else {
            throw new RunException(__('The HTTP verb used is not available.'), array(
                'code' => 'RPP-020',
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp020'
            ), 500);
        }
    }

}