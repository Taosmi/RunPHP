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
    private $statusCode;


    /**
     * Initialize the response. The source should be an HTML view or a data
     * structure.
     *
     * @param mixed  $source  A view name or a data structure or nothing.
     * @param mixed  $vars    A collection of variables or the HTTP status code (optional).
     */
    public function __construct ($source = array(), $vars = null) {
        if (is_string($source)) {
            $this->html = $source;
            $this->data = $vars;
        } else {
            $this->data = $source;
            $this->statusCode = $vars ? $vars : 200;
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
        // Set the HTTP status code.
        header('HTTP/1.1 '.$this->statusCode);
        // Check if the response can be rendered as requested.
        if ($this->html) {
            $format = 'html';
        } else if ($format === 'html') {
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
     * Display a template from inside a view. Very useful to separate common
     * visual segments (headers, footer, etc.) into independent files.
     *
     * @param string $template  The name of the template.
     * @param array  $data      Data that can be used inside the template.
     * @throws                  ErrorException if the template does not exist.
     */
    public function template ($template, $data = null) {
        // Check if the template content exists.
        $templateFile = APP.$template.'.php';
        if (!file_exists($templateFile)) {
            throw new ErrorException(__('The HTML template does not exist.', 'system'), array(
                'code' => 'RPP-020',
                'template' => $template,
                'file' => $templateFile,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp020'
            ));
        }
        // Include the template file.
        extract($data);
        require($templateFile);
    }


    /**
     * Render the HTML view to the system output.
     *
     * @throws  ErrorException if the view does not exist.
     */
    private function renderHTML () {
        // Set the HTML file.
        $file = APP.$this->html.'.php';
        if (!file_exists($file)) {
            throw new ErrorException(__('The view does not exist.', 'system'), array(
                'code' => 'RPP-021',
                'view' => $this->html,
                'file' => $file,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp021'
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