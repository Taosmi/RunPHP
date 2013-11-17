<?php

namespace ProWeb;


/**
 * This class implements the 404 error Exception.
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
class Error404Exception extends ErrorException {

    /**
     * Initializes the 404 Exception.
     * 
     * @param url         A string with the URL.
     * @param controller  A string with the missing controller.
     */
    public function __construct ($url, $controller) {
        parent::__construct('404', array(
            'url' => $url,
            'controller' => $controller
        ));
    }
}
?>