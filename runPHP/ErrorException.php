<?php

namespace runPHP;

/**
 * This class implements an extended version of an Exception with additional
 * data as an array that will be available when handling the error. It is also
 * possible to assign an HTTP status code to be used at the response.
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
class ErrorException extends \Exception {

    /**
     * An error message.
     * @var string
     */
    public $msg;

    /**
     * Additional error information.
     * @var array
     */
    public $data;

    /**
     * An HTTP status code.
     * @var int
     */
    public $httpStatus;

    /**
     * Initialize a new error exception. The error exception may have some
     * additional information and a HTTP status code to use at the response
     * (default value 500).
     *
     * @param string  $msg         A description of the error.
     * @param array   $data        Additional error information (optional).
     * @param int     $httpStatus  The HTTP status code (optional).
     */
    public function __construct ($msg, $data = array(), $httpStatus = 500) {
        parent::__construct($msg);
        $this->msg = $msg;
        $this->data = $data;
        $this->httpStatus = $httpStatus;
    }
}