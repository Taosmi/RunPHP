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
     * @var int  An HTTP status code.
     */
    private $statusCode;


    /**
     * Initialize the response.
     *
     * @param array    $vars      A collection of variables (optional).
     * @param integer  $httpCode  The http response code (default value 200).
     */
    public function __construct ($vars = null, $httpCode = 200) {
        $this->data = $vars;
        $this->statusCode = $httpCode;
    }


    /**
     * Render the response.
     *
     * @param array  $request  The request information.
     */
    public function render ($request) {
        // Get the format from the request.
        $format = $request['format'];
        // Get the format from the header.
        if (!$format) {
            $format = $request['mime'];
        }
        // Render the response.
        switch ($format) {
        case 'application/json': case 'json':
            Logger::sys(__('Rendering JSON view.', 'system'));
            // Set the console information.
            if (CONSOLE) {
                $this->data['_console'] = Logger::getLog();
            }
            // Render the data structure as JSON by default.
            $this->renderJSON();
            break;
        case 'application/xml': case 'xml':
            Logger::sys(__('Rendering XML.', 'system'));
            // Set the console information.
            if (CONSOLE) {
                $this->data['_console'] = Logger::getLog();
            }
            // Render the data structure as XML.
            $this->renderXML();
            break;
        default:
            // Render the response as HTML.
            Logger::sys(__('Rendering HTML view.', 'system'));
            if ($this->isError()) {
                // Set the application specific HTML error or the framework HTML error.
                $file = file_exists(VIEWS_ERRORS.'/error.php') ? VIEWS_ERRORS.'/error' : SYS.'/html/error';
            } else {
                $file = Router::getView($request);
                $this->data['params'] = $request['params'];
            }
            $this->renderHTML($file);
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
     * Display a pattern from inside a view. Very useful to separate common
     * visual segments (headers, footer, etc.) into independent files.
     *
     * @param string  $pattern  The name of the pattern.
     * @param array   $data     Data that can be used inside the pattern.
     */
    public function pattern ($pattern, $data = null) {
        // Check if the template content exists.
        $patternFile = VIEWS_PATTERNS.$pattern.'.php';
        if (!file_exists($patternFile)) {
            _e('The HTML pattern does not exist.', 'system');
            echo '('.$pattern.')';
        } else {
            // Include the template file.
            extract($data);
            require($patternFile);
        }
    }


    /**
     * If there is an HTTP error code, return true otherwise return false.
     * The HTTP error codes are in the range from 400 to 600.
     *
     * @return bool  True if there is an HTTP error code. False otherwise.
     */
    private function isError () {
        return ($this->statusCode > 399 and $this->statusCode < 600);
    }

    /**
     * Render the HTML view to the output system. If the view does not exist or
     * an exception is raised, then render the error page.
     *
     * @param  string  $file  The view file.
     */
    private function renderHTML ($file) {
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
            $file = file_exists(VIEWS_ERRORS.'/notFoundError.php') ? VIEWS_ERRORS.'/notFoundError' : SYS.'/html/notFoundError';
        }
        // Set the HTTP status code.
        header('HTTP/1.1 '.$this->statusCode);
        header('Content-Type: text/html');
        // Extract the data and include the view file.
        extract($this->data);
        include($file.'.php');
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