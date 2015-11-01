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

}