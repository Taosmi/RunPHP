<?php

namespace proWeb\plugins;
use proWeb\Logger;

/**
 * This class implements the functionality to render a data structure with JSON
 * format.
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
class JsonView {

    /**
     * The internal Json data holder.
     */
    private $data;


    /**
     * Initializes the Json data.
     *
     * @param array $data  The data structure to show as JSON (optional).
     */
    public function __construct ($data = array()) {
        $this->data = $data;
    }


    /**
     * Sets a key - value pair to the JSON data.
     *
     * @param string $key    The key name.
     * @param string $value  The key value.
     */
    public function set ($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Encodes the data into a JSON string and displays the result to the
     * output system.
     */
    public function render () {
        Logger::sys(__('Encoding JSON View.', 'system'));
        echo json_encode($this->data);
    }
}