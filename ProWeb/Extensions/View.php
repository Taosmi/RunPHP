<?php

namespace Taosmi\ProWeb\Extensions;

use Taosmi\ProWeb\Core\Logger;
use Taosmi\ProWeb\Core\Extension;
use Taosmi\ProWeb\Core\ErrorException;


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
class eView extends Extension {

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
        Logger::sys('Loading View ('.$viewName.')');
        return new View($viewName, $this->options);
    }
}


/**
 * This class implements the functionality to load a file as a View. This file 
 * may have variables that will be replaced by data at render time. The view 
 * must only have visual information, the business logic must take place at the 
 * Model layer.
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
class View {

    /**
     * The name of the view, the view file, the view options and the variables 
     * that will be accessible from inside the view content.
     */
    private $name, $file, $options, $varsPage = array();

    /**
     * Checks if the view file exists.
     * 
     * @param name     a string with the name of the view
     * @param options  an associative array with the view options
     * @throws         EXTException() if the view does not exist
     */
    public function __construct ($name, &$options) {
        // Stores private data.
        $this->name = $name;
        $this->options = $options;
        // Checks if the view content exists.
        $this->file = $this->options['viewsPath'].$this->name.'.php';
        if (!file_exists($this->file)) {
            throw new ErrorException('', array(
                'view' => $this->name,
                'file' => $file
            ));
        }
    }

    /**
     * Sets a variable to be accessible from the page.
     * 
     * @param key    a string with the variable name
     * @param value  the variable value
     */
    public function set ($key, $value) {
        $this->varsPage[$key] = $value;
    }

    /**
     * Exposes the variables and displays the view content to the output 
     * system. If the console is set to be displayed, it will be rendered by 
     * default as HTML content. A content type should be provided to avoid 
     * errors when rendering the console with no HTML content. Available types:
     *      'json' => the console will be displayed as JSON
     *      'xml'  => the console will be displayed as XML
     *      'html' => the console will be displayed as HTML (default)
     * 
     * @param type  a string with the content type (optional)
     */
    public function render ($type = null) {
        // Extracts the data and includes the view file.
        extract($this->varsPage);
        require($this->file);
        // Includes the Log Console if the debug configuration is conveniently set.
/*
        if ($this->options['log']) {
            $console = Logger::getLog();
            var_dump($console);
            switch ($type) {
                case 'xml':
                    //TODO magarcia
                    break;
                case 'json':
                    // TODO magarcia
                    break;
                case 'html': default:
                    require(SYSTEM.'/htmlConsole.php');
            }
        }
 */
    }

    /**
     * Displays a template from inside a view. Very useful to separate common 
     * visual segments (headers, footer, etc.) into independent files.
     * 
     * @param template  a string with the name of the template
     * @param dynData   an array that should be accessible inside the template
     * @throws          EXTException() if the template does not exist
     */
    public function renderTemplate ($template, $dynData = null) {
        // Checks if the template content exists.
        $templateFile = $this->options['templatesPath'].$template.'.php';
        if (!file_exists($templateFile)) {
            throw new ErrorTException('', array(
                'view' => $template,
                'file' => $templateFile
            ));
        }
        // Includes the template file.
        require($templateFile);
    }
}
?>