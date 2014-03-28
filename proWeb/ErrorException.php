<?php

namespace proWeb;

/**
 * This class implements an extended version of an Exception with additional 
 * data as an array so it will be available when handling the error. It also
 * includes a type that helps to choose which view will be used to show the
 * error.
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
     * An array with the additional error information.
     */
    public $data;

    /**
     * A string with the error exception type that will be bound to a specific view.
     */
    public $type;


    /**
     * Initializes a new error exception.
     * The error exception may have some additional information and may be of a
     * specific type of error. This type helps to choose which view will be used
     * to show the error to the final user.
     *
     * @param int    $code  An error code.
     * @param string $msg   A description of the error.
     * @param array  $data  Additional error information (optional).
     * @param string $type  An error exception type (optional).
     */
    public function __construct ($code, $msg, $data = array(), $type = null) {
        parent::__construct($msg, $code);
        $this->data = $data;
        $this->type = $type;
    }
}