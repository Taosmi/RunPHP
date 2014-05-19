<?php

namespace proWeb;

/**
 * This class implements the functionality to render a response as HTML, JSON
 * data structure or XML data structure.
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
class Response {

    /**
     * The internal html view name and the data holder.
     */
    private $html, $data;

    /**
     * The HTTP status code.
     */
    private $status = 200;


    /**
     * Initializes the response. The source should be a HTML view filename or a
     * data structure.
     *
     * @param mixed $source  A view name or a data structure.
     * @param array $vars    A collection of variables to be accessible from the view.
     */
    public function __construct ($source, $vars = null) {
        if (is_string($source)) {
            $this->html = $source;
            $this->data = $vars;
        } else if (is_object($source)) {
            $this->data = get_object_vars($source);
        } else {
            $this->data = $source;
        }
    }


    /**
     * Sets a key - value pair to the data store.
     *
     * @param string $key    The key name.
     * @param string $value  The key value.
     */
    public function setData ($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Renders the response with an specific format. If no format is available,
     * renders the response as HTML.
     *
     * @param string $format  The format to render the response (optional).
     * @throws                SystemException if the HTML view does not exist.
     */
    public function render ($format) {
        // Render a HTML view.
        if ($this->html) {
            Logger::sys(__('Loading HTML View "%s".', 'system'), $this->html);
            $this->renderHTML();
        } else {
            // Set the console information.
            if (SHOW_CONSOLE) {
                $this->data['_console'] = Logger::getLog();
            }
            // Renders a data structure.
            switch ($format) {
            case 'xml':
                // Render the data structure as XML.
                Logger::sys(__('Rendering XML view.', 'system'));
                echo 'XML view not available.';
                break;
            case 'json':
            default:
                // Render the data structure as JSON.
                Logger::sys(__('Rendering JSON view.', 'system'));
                echo json_encode($this->data);
            }
        }
    }

    /**
     * Renders the HTML view to the output.
     *
     * @throws  SystemException if the view does not exist.
     */
    private function renderHTML () {
        // Set the HTML file.
        $file = APP.$this->html.'.php';
        if (!file_exists($file)) {
            throw new SystemException(__('The view does not exist.', 'system'), array(
                'code' => 'PPW-020',
                'view' => $this->html,
                'file' => $file
            ));
        }
        // Extracts the data and includes the view file.
        extract($this->data);
        require($file);
        // Shows the HTML console.
        if (SHOW_CONSOLE) {
            require(SYSTEM.'/html/console.php');
        }
    }
}