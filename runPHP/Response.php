<?php

namespace runPHP;

/**
 * This class implements the functionality to render a response as HTML or as
 * JSON or as XML.
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
     * The internal html view name.
     * @var string
     */
    private $html;

    /**
     * The data holder.
     * @var mixed
     */
    private $data;

    /**
     * The HTTP status code.
     * @var int
     */
    private $status = 200;


    /**
     * Initialize the response. The source should be an HTML view or a data
     * structure.
     *
     * @param mixed  $source  A view name or a data structure or nothing.
     * @param array  $vars    A collection of variables to be accessible from the view (optional).
     */
    public function __construct ($source = array(), $vars = null) {
        if (is_string($source)) {
            $this->html = $source;
            $this->data = $vars;
        } else {
            $this->data = $source;
        }
    }


    /**
     * Set a key - value pair to the data store.
     *
     * @param string  $key    The key name.
     * @param mixed   $value  The key value.
     */
    public function setData ($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * Render the response with an specific format (HTML as default).
     *
     * @param string  $format  The format to render the response.
     */
    public function render ($format) {
        // Check if the response can be rendered as requested.
        if ($this->html) {
            $format = 'html';
        } else if (!$format || $format === 'html') {
            $format = 'json';
        }
        // Set the console information for JSON and XML.
        if (CONSOLE && $format !== 'html') {
            $this->data['_console'] = Logger::getLog();
        }
        // Render the response.
        switch ($format) {
        case 'html':
            // Render the response as HTML.
            Logger::sys(__('Rendering HTML view.', 'system'));
            $this->renderHTML();
            break;
        case 'json':
            // Render the data structure as JSON.
            Logger::sys(__('Rendering JSON view.', 'system'));
            echo json_encode($this->data);
            break;
        case 'xml':
            // Render the data structure as XML.
            Logger::sys(__('Rendering XML view.', 'system'));
            $this->renderXML();
            break;
        }
    }


    /**
     * Render the HTML view to the system output.
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
                'file' => $file,
                'helpLink' => 'http://runphp.taosmi.es/faq/ppw020'
            ));
        }
        // Extract the data and includes the view file.
        extract($this->data);
        require($file);
        // Show the HTML console.
        if (CONSOLE) {
            require(SYSTEM.'/html/console.php');
        }
    }

    /**
     * Render the response as XML. (WIP)
     */
    private function renderXML() {
        echo 'WIP: XML view not available.';
    }
}