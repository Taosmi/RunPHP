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
     * The render style (html or data).
     * @var string
     */
    private $style;

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
     * Initialize the response. The response style should be 'html' to render
     * a view with the same name as the controller or 'data' to render a JSON or
     * XML data structure.
     *
     * @param string  $style     The response style should be 'html' or 'data'.
     * @param array   $vars      A collection of variables (optional).
     * @param integer $httpCode  The http response code (default value 200).
     */
    public function __construct ($style, $vars = null, $httpCode = 200) {
        $this->style = $style;
        $this->data = $vars;
        $this->statusCode = $httpCode;
    }


    /**
     * @return bool
     */
    public function isError () {
        return ($this->statusCode > 399 and $this->statusCode < 600);
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
     * @param array  $controller  The controller information.
     */
    public function render ($controller) {
        // Set the HTTP status code.
        header('HTTP/1.1 '.$this->statusCode);
        // Render the response.
        switch ($this->style) {
        case 'data':
            // Set the console information for JSON and XML.
            $this->data['_console'] = Logger::getLog();
            if ($controller['format'] === 'xml') {
                // Render the data structure as XML.
                Logger::sys(__('Rendering XML view.', 'system'));
                $this->renderXML();
            } else {
                // Render the data structure as JSON by default.
                Logger::sys(__('Rendering JSON view.', 'system'));
                echo json_encode($this->data);
            }
            break;
        case 'html':
        default:
            // Render the response as HTML.
            Logger::sys(__('Rendering HTML view.', 'system'));
            $this->renderHTML($controller);
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
        $templateFile = VIEWS.$template.'.php';
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
     * @param  array $controller
     * @throws  ErrorException if the view does not exist.
     */
    private function renderHTML ($controller) {
        // Set the HTML file.
        $file = VIEWS.$controller['path'].$controller['name'].'.php';
        // Check if the file exists.
        if (!file_exists($file)) {
            $this->statusCode = 404;
            $exception = new ErrorException(__('The view does not exist.', 'system'), array(
                'code' => 'RPP-021',
                'view' => $this->html,
                'file' => $file,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp021'
            ));
            Logger::error($exception);
        }
        // If there is an error show the error HTML.
        if ($this->isError()) {
            // Set the application specific HTML error or the framework HTML error.
            $errorPage = ($this->statusCode == 404) ? '/notFoundError' : '/error';
            $errorView = VIEWS_ERRORS.$errorPage.'.php';
            // Extract the data and includes the view file.
            extract($this->data);
            if (file_exists($errorView)) {
                include($errorView);
            } else {
                include(SYSTEM.'/html'.$errorPage.'.php');
            }
        } else {
            // Extract the data and includes the view file.
            extract($this->data);
            include($file);
            // Show the HTML console.
            if (CONSOLE) {
                require(SYSTEM.'/html/console.php');
            }
        }
    }

    /**
     * Render the response as XML. (WIP)
     */
    private function renderXML() {
        echo 'WIP: XML view not available.';
    }
}