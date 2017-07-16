<?php

namespace runPHP;

/**
 * Analyze the request to know which application and controller (API or View) is
 * involved to load and run it.
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
class Router {

    /**
     * Analyze the HTTP request and retrieve the relevant information.
     *
     * @return array  The request information.
     */
    public static function getRequest () {
        // Get the URL and the MIME type.
        $uri = parse_url($_SERVER['REQUEST_URI']);
        $ext = pathinfo($uri['path'], PATHINFO_EXTENSION);
        if ($ext) {
            $mime = 'application/'.$ext;
            $url = substr($uri['path'], 0, -(strlen($ext) + 1));
        } else {
            $mime = array_key_exists('CONTENT_TYPE', $_SERVER)
                ? current(explode(';', $_SERVER['CONTENT_TYPE']))
                : current(explode(',', $_SERVER['HTTP_ACCEPT']));
            $url = $uri['path'];
        }
        // Get a controller for this request based on the MIME type.
        switch ($mime) {
            // API controller.
            case 'application/json': case 'json':
            case 'application/xml': case 'xml':
                $controller = self::getController(APP, $url);
                $controllerClass = str_replace('/', '\\', substr($controller, strlen(APP) + 1));
                $params = explode('/', substr(APIS.$url, strlen($controller) + 1));
                break;
            // View controller (default).
            case 'text/html': case 'html':
            default:
                $controller = self::getController(VIEWS, $url);
                $controllerClass = 'runPHP\\ViewController';
                $params = explode('/', substr(VIEWS.$url, strlen($controller) + 1));
        }
        // Return the request data.
        return array(
            'uid' => uniqid(),
            'uri' => $uri['path'],
            'app' => $_SERVER['SERVER_NAME'],
            'from' => $_SERVER['REMOTE_ADDR'],
            'url' => $url,
            'query' => array_key_exists('query', $uri) ? $uri['query'] : '',
            'method' => $_SERVER['REQUEST_METHOD'],
            'mime' => $mime,
            'date' => date('Y-m-d H:i:s'),
            'ctrl' => $controller,
            'ctrlClass' => $controllerClass,
            'params' => $params,
            'user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
            'secret' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : ''
        );
    }


    /**
     * Get a backward class/file name iterating throw the URL hierarchy.
     * If no class name available, return null.
     *
     * @param  string  $root     Static part of an URL.
     * @param  string  $path     An URL to iterate throw.
     * @return string            A class/file name or null.
     */
    private static function getBackwardPath ($root, $path) {
        if ($path) {
            // Decompose the path into a hierarchy.
            $pathParts = explode('/', $path);
            $bwPath = array_shift($pathParts);
            // Go across the directory hierarchy.
            while (is_dir($root.$bwPath.DIRECTORY_SEPARATOR.current($pathParts))) {
                $bwPath.= DIRECTORY_SEPARATOR.array_shift($pathParts);
            }
            // Check if the file exist.
            $file = array_shift($pathParts);
            if (file_exists($root.$bwPath.DIRECTORY_SEPARATOR.$file.'.php')) {
                // Return the class/file name.
                return $root.$bwPath.DIRECTORY_SEPARATOR.$file;
            }
        }
        return null;
    }

    /**
     * Get a controller for the current request. The controller may be an API
     * controller or a View controller.
     *
     * @param  string   $ctrlFolder  A folder related with the controller.
     * @param  string   $url         An URL.
     * @return string                A controller path or null.
     */
    private static function getController ($ctrlFolder, $url) {
        // Get a controller.
        $ctrl = '';
        if (file_exists($ctrlFolder.$url.'.php')) {
            $ctrl = $ctrlFolder.$url;
        } else {
            // If the URL is a folder, try to get an index controller.
            if (is_dir($ctrlFolder.$url) && file_exists($ctrlFolder.$url.'index.php')) {
                $ctrl = $ctrlFolder.$url.'index';
            } else {
                // If no controller, try to get a backward controller.
                $ctrl = self::getBackwardPath($ctrlFolder, $url);
            }
        }
        return $ctrl;
    }
}