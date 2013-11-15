<?php

namespace ProWeb\Extensions;
use ProWeb;


/**
 * This class is a core extension. Implements the functionality to load a View 
 * object. Once loaded, a View object holds the content that will be rendered 
 * to the output system. Variables can be set so they will be available from 
 * the content.
 * 
 * @author Miguel Angel Garcia
 * 
 * Copyright 2012 TAOSMI Technology
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
class ViewExt extends ProWeb\Extension {

    /**
     * View options (views path, templates path and debug configuration).
     */
    private $options;


    /**
     * Initiates the extension.
     */
    public function init () {
        // Stores the configuration.
        $this->options = array(
            'viewsPath' => APP.$this->controller->cfg['PATHS']['views'],
            'templatesPath' => APP.$this->controller->cfg['PATHS']['templates']
        );
    }

    /**
     * Loads a View.
     * 
     * @param viewName  A string with the view name.
     * @return          The requested view object.
     */
    public function load ($viewName) {
        // Loads and returns the view.
        ProWeb\Logger::sys('Loading View "%s"', $viewName);
        return new View($viewName, $this->options);
    }
}
?>