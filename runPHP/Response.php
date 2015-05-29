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
        // Render the response.
        switch ($this->style) {
        case 'data':
            // Set the console information for JSON and XML.
            if (CONSOLE) {
                $this->data['_console'] = Logger::getLog();
            }
            if ($controller['format'] === 'xml') {
                // Render the data structure as XML.
                Logger::sys(__('Rendering XML view.', 'system'));
                $this->renderXML();
            } else {
                // Render the data structure as JSON by default.
                Logger::sys(__('Rendering JSON view.', 'system'));
                $this->renderJSON();
            }
            break;
        case 'html':
        default:
            // Render the response as HTML.
            Logger::sys(__('Rendering HTML view.', 'system'));
            $this->renderHTML($controller['path'], $controller['name']);
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
        $templateFile = VIEWS_TEMPLATES.$template.'.php';
        if (!file_exists($templateFile)) {
            Logger::error(new ErrorException(__('The HTML template does not exist.', 'system'), array(
                'code' => 'RPP-020',
                'file' => $templateFile,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpp020'
            )));
            //$this->renderHTMLError($exception);
        } else {
            // Include the template file.
            extract($data);
            require($templateFile);
        }
    }


    /**
     * Render the HTML view to the output system. If the view does not exist,
     * render the not found page. If there is an exception, render the error
     * page.
     *
     * @param  string  $path  The view path.
     * @param  string  $name  The view name.
     */
    private function renderHTML ($path, $name) {
        // Render the HTML error view if there is an error.
        if ($this->isError()) {
            // Set the application specific HTML error or the framework HTML error.
            $file = VIEWS_ERRORS.'/error';
            // Set the framework HTML error, if the application HTML error doesn't exist.
            if (!file_exists($file.'.php')) {
                $file = SYSTEM.'/html/error';
            }
        } else {
            // Render the HTML view.
            $file = VIEWS.$path.'/'.$name;
            // Set the application HTML error view, if the view does not exist.
            if (!file_exists($file.'.php')) {
                // Set the error info.
                $this->statusCode = 404;
                $this->data = array(
                    'error' => array(
                        'msg' => __('The view does not exist.', 'system'),
                        'data' => array(
                            'code' => 'RPP-021',
                            'file' => $file
                        ),
                        'helpLink' => 'http://runphp.taosmi.es/faq/rpp021'
                    )
                );
                // Set the application HTML view.
                $file = VIEWS_ERRORS.'/notFoundError';
                // Set the framework HTML error, if the application HTML error view does not exist.
                if (!file_exists($file.'.php')) {
                    $file = SYSTEM.'/html/notFoundError';
                }
            }
        }
        // Set the HTTP status code.
        header('HTTP/1.1 '.$this->statusCode);
        // Extract the data and include the view file.
        extract($this->data);
        include($file.'.php');
        // Show the HTML console.
        if (CONSOLE) {
            require(SYSTEM.'/html/console.php');
        }
    }

    /**
     * Render the response as JSON.
     */
    private function renderJSON () {
        header('HTTP/1.1 '.$this->statusCode);
        echo json_encode($this->data);
    }

    /**
     * Render the response as XML. (WIP)
     */
    private function renderXML () {
        header('HTTP/1.1 '.$this->statusCode);
        echo 'WIP: XML view not available.';
    }
}