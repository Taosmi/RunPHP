<?php

namespace ProWeb\Extensions;
use ProWeb;

/**
 * This class implements the functionality to load a file as a View. This file 
 * may have variables that will be replaced by data at render time. The view 
 * must only have visual information, the business logic must take place at the 
 * domain layer.
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
     * @param string $name     The name of the view.
     * @param array  $options  The view options.
     * @throws \ProWeb\ErrorException()  If the view does not exist.
     */
    public function __construct ($name, &$options) {
        // Stores private data.
        $this->name = $name;
        $this->options = $options;
        // Checks if the view content exists.
        $this->file = $this->options['viewsPath'].$this->name.'.php';
        if (!file_exists($this->file)) {
            throw new ProWeb\ErrorException('1100', __('The view does not exist.', 'View'), array(
                'view' => $this->name,
                'file' => $this->file
            ));
        }
    }


    /**
     * Sets a variable to be accessible from the page.
     * 
     * @param string $key    The variable name.
     * @param string $value  The variable value.
     */
    public function set ($key, $value) {
        $this->varsPage[$key] = $value;
    }

    /**
     * Exposes the variables and displays the view content to the output 
     * system.
     */
    public function render () {
        // Extracts the data and includes the view file.
        extract($this->varsPage);
        require($this->file);
    }

    /**
     * Displays a template from inside a view. Very useful to separate common 
     * visual segments (headers, footer, etc.) into independent files.
     * 
     * @param string $template  The name of the template.
     * @param array  $dynData   Data that should be accessible inside the template.
     * @throws \ProWeb\ErrorException()  If the template does not exist.
     */
    public function renderTemplate ($template, $dynData = null) {
        // Checks if the template content exists.
        $templateFile = $this->options['templatesPath'].$template.'.php';
        if (!file_exists($templateFile)) {
            throw new ProWeb\ErrorException('1101', __('The template does not exist.', 'View'), array(
                'template' => $template,
                'file' => $templateFile
            ));
        }
        // Includes the template file.
        require($templateFile);
    }
}