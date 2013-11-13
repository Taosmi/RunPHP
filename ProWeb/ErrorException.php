<?php

namespace ProWeb;


/**
 * This class implements an extended version of an Exception with additional 
 * data as an array so it will be available when handling the error.
 * 
 * @author Miguel Angel Garcia
 * 
 * Copyright 2013 TAOSMI Technology
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
     * Initializes the Base Exception.
     * 
     * @param code     A string with the error code.
     * @param dynData  An array with additional error data.
     */
    public function __construct ($code, $dynData = array()) {
        parent::__construct($code);
        $this->data = $dynData;
    }
}
?>