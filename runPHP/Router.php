<?php

namespace runPHP;

/**
 * Analyze the request to know which application and which API is involved to
 * load and run them.
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
     * Get the API name involved with the request. If no API is available,
     * returns null.
     *
     * @param  array   $request  A request information.
     * @return string            The API name or null.
     */
    public static function getApi (&$request) {
        // Build the API full class name.
        $api = APIS.$request['path'].'/'.$request['name'];
        // Check the API and get the class namespace.
        if (file_exists(APP.$api.'.php')) {
            return str_replace('/', '\\', substr($api, 1));
        }
        // Check if there is a backwards API.
        if ($request['path']) {
            $path = '';
            $root = APP.APIS;
            $pathParts = explode('/', substr($request['path'], 1));
            // Loop throw the URL path.
            while ($pathParts) {
                if (!file_exists($root.$path.'/'.$pathParts[0].'.php') && !is_dir($root.$path.'/'.$pathParts[0])) {
                    break;
                }
                $path .= '/'.array_shift($pathParts);
            }
            // If a part of the path is valid, set the rest as parameters.
            if ($path && !is_dir($root.$path)) {
                $request['params'] = $pathParts;
                $request['params'][] = $request['name'];
                // Return the backwards controller.
                return str_replace('/', '\\', APIS.$path);
            }
        }
        return null;
    }

    /**
     * Analyze the HTTP request and retrieve the relevant request information.
     *
     * @return array  The request information.
     */
    public static function getRequest () {
        // Get the relevant request data.
        $url = pathinfo(str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
        return array(
            'app' => $_SERVER['SERVER_NAME'],
            'cfg' => parse_ini_file(WEBAPPS.DIRECTORY_SEPARATOR.$_SERVER['SERVER_NAME'].DIRECTORY_SEPARATOR.'app.cfg', true),
            'from' => $_SERVER['REMOTE_ADDR'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'url' => $_SERVER['REQUEST_URI'],
            'path' => $url['dirname'] === '/' ? '' : $url['dirname'],
            'name' => $url['filename'] ? $url['filename'] : 'index',
            'format' => $url['extension'],
            'params' => array()
        );
    }
}