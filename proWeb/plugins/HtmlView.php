<?php

namespace proWeb\plugins;
use proWeb\SystemException, proWeb\Logger;

/**
 * This class implements the functionality to load an HTML file as a View. This
 * file may have variables that will be replaced by data at render time. The view
 * must only have visual information, the business logic must take place at the
 * domain layer.
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
class HtmlView {

    /**
     * The view file and the variables that will be accessible from inside the
     * view content.
     */
    private $file, $varsPage = array();


    /**
     * Checks if the view file exists.
     *
     * @param string $name  The name of the view.
     * @throws              SystemException if the view does not exist.
     */
    public function __construct ($name) {
        // Stores private data.
        $this->name = $name;
        // Checks if the view content exists.
        $this->file = APP.$this->name.'.php';
        Logger::sys(__('Loading HTML View "%s".', 'system'), $name);
        if (!file_exists($this->file)) {
            throw new SystemException('HVW-01', __('The view does not exist.', 'system'), array(
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
        // Shows the HTML console.
        if (SHOW_CONSOLE) {
            require(SYSTEM.'/html/console.php');
        }
    }

    /**
     * Displays a template from inside a view. Very useful to separate common
     * visual segments (headers, footer, etc.) into independent files.
     *
     * @param string $template  The name of the template.
     * @throws                  SystemException if the template does not exist.
     */
    public function renderTemplate ($template) {
        // Checks if the template content exists.
        $templateFile = APP.$template.'.php';
        if (!file_exists($templateFile)) {
            throw new SystemException('HVW-02', __('The HTML template does not exist.', 'system'), array(
                'template' => $template,
                'file' => $templateFile
            ));
        }
        // Includes the template file.
        require($templateFile);
    }
}