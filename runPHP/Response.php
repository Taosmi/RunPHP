<?php

namespace runPHP;

/**
 * This class implements the functionality to render a response as HTML, as
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
     * @var mixed  A data holder.
     */
    private $data;

    /**
     * @var string  An HTML file to render.
     */
    private $file;

    /**
     * @var int  An HTTP status code.
     */
    private $statusCode;


    /**
     * Initialize the response.
     *
     * @param array    $vars      A collection of variables (optional: default null).
     * @param integer  $httpCode  The http response code (optional: default 200).
     */
    public function __construct ($vars = null, $httpCode = 200) {
        $this->data = $vars;
        $this->statusCode = $httpCode;
    }


    /**
     * Display a pattern from inside a view. Very useful to separate common
     * visual segments (headers, footer, etc.) into independent files.
     *
     * @param string  $pattern  A name of the pattern.
     * @param array   $data     The data that may be used inside the pattern.
     */
    public function pattern ($pattern, $data = array()) {
        // Check if the template file exist.
        $patternFile = VIEWS_PATTERNS.$pattern.'.php';
        if (!file_exists($patternFile)) {
            _e('The HTML pattern does not exist.', 'system');
            echo '('.$pattern.')';
        } else {
            // Include the template file.
            extract(array_merge($this->data, $data));
            include($patternFile);
        }
    }

    /**
     * Render a response based on the request MIME type.
     *
     * @param string  $format  A MIME type of the response.
     */
    public function render ($format) {
        // Render the response.
        switch ($format) {
        case 'application/json': case 'json':
            // Render the data structure as JSON by default.
            Logger::sys(__('Rendering JSON response.', 'system'));
            $this->data['_console'] = CONSOLE ? Logger::getLog() : '';
            $this->renderJSON();
            break;
        case 'application/xml': case 'xml':
            // Render the data structure as XML.
            Logger::sys(__('Rendering XML response.', 'system'));
            $this->data['_console'] = CONSOLE ? Logger::getLog() : '';
            $this->renderXML();
            break;
        case 'text/html':case 'html': default:
            // Render the response as HTML.
            Logger::sys(__('Rendering HTML View.', 'system'));
            $this->renderHTML();
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
     * Set a file as HTML response.
     *
     * @param string  $file  A file with HTML code.
     */
    public function setFile ($file) {
        $this->file = $file.'.php';
    }


    /**
     * Render the HTML view to the output system. If the view does not exist or
     * an exception is raised, then render the error page.
     */
    private function renderHTML () {
        // Set the HTTP status code.
        header('HTTP/1.1 '.$this->statusCode);
        header('Content-Type: text/html');
        // Extract the data and include the view file.
        extract($this->data);
        include($this->file);
        // Show the HTML console.
        if (CONSOLE) {
            require(SYS.'/html/console.php');
        }
    }

    /**
     * Render the response as JSON.
     */
    private function renderJSON () {
        header('HTTP/1.1 '.$this->statusCode);
        header('Content-Type: application/json');
        if ($this->data) {
            echo json_encode($this->data);
        }
    }

    /**
     * Render the response as XML. (WIP)
     */
    private function renderXML () {
        header('HTTP/1.1 '.$this->statusCode);
        header('Content-Type: application/xml');
        echo 'WIP: XML view not available.';
    }
}