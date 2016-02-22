<?php

namespace runPHP;

/**
 * An extended version of an Exception with additional data as an array that
 * will be available when handling an error. It also assigns an HTTP status
 * code to be used at the response.
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
class RunException extends \Exception {


    /**
     * @var array  Additional error information.
     */
    public $data;

    /**
     * @var int  An HTTP status code.
     */
    public $httpStatus;

    /**
     * @var string  An error message.
     */
    public $msg;


    /**
     * Initialize a new error exception. The error exception may have an HTTP
     * status code and an error message. Additional information is optional.
     *
     * @param int     $httpStatus  The HTTP status code.
     * @param string  $msg         A description of the error.
     * @param array   $data        Additional error information (optional).
     */
    public function __construct ($httpStatus, $msg, $data = array()) {
        parent::__construct($msg);
        $this->msg = $msg;
        $this->data = $data;
        $this->httpStatus = $httpStatus;
    }
}