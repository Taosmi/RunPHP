<?php

/**
 * Analyzes the request to know which application and which controller are
 * involved to load and run them.
 *
 * @author Miguel Angel Garcia
 *
 * Copyright 2012 TAOSMI Technology
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
namespace ProWeb {

    /**
     * Shows an error page that matches the type error if available.
     * If no type error available shows the default error page.
     * If no default error page shows the framework error page.
     *
     * @param ErrorException $exception  An error exception.
     */
    function doError ($exception) {
        // Logs the error.
        Logger::error($exception);
        $errorPath = APP.'/views/errors/';
        $errorPage = $exception->type.'Error.php';
        if (file_exists($errorPath.$errorPage)) {
            // Shows the application specific error page.
            include($errorPath.$errorPage);
        } else if (file_exists($errorPath.'error.php')) {
            // Shows the application default error page.
            include($errorPath.'error.php');
        } else if (file_exists(SYSTEM.'/html/'.$exception->type.'Error.php')) {
            // Shows the system specific error page.
            include(SYSTEM.'/html/'.$exception->type.'Error.php');
        } else {
            // Shows the system fatal error page.
            include(SYSTEM.'/html/error.php');
        }
    }

    /**
     * Loads the application configuration file involved in a request.
     * If the application configuration is not available, returns an empty array.
     * 
     * @param array $request  A request information.
     * @return array          An application configuration or an empty array.
     */
    function getAppConfig ($request) {
        // The request must belong to an application.
        if (!$request['appName']) {
            return array();
        }
        // Loads the application configuration file.
        $cfgFile = WEBAPPS.DIRECTORY_SEPARATOR.$request['appName'].DIRECTORY_SEPARATOR.'config.ini';
        if (!file_exists($cfgFile)) {
            return array();
        }
        // Returns the configuration properties.
        return parse_ini_file($cfgFile, true);
    }

    /**
     * Gets the controller file involved and returns a new instance of it. If no
     * controller is available, returns null.
     *
     * @param array $cfg       An application configuration.
     * @param array $request   A request information.
     * @return Controller      A controller object or null.
     */
    function getController ($cfg, $request) {
        // The controller root, the controller name and the extra parameters.
        $root = $cfg['PATHS']['controllers'].DIRECTORY_SEPARATOR;
        $controller = $request['controller'];
        $extraParam = array();
        // If it is a folder, tries the default index controller.
        if (is_dir(APP.$root.$controller)) {
            $controller.= DIRECTORY_SEPARATOR.'index';
        }
        // Tries to find a controller.
        $loops = 2;
        while ($loops && $controller) {
            if (file_exists(APP.$root.$controller.'.php')) {
                $request['controller'] = $controller;
                $request['resource'] = implode($extraParam);
                // Converts the controller name to a name-space class.
                $controllerName = substr($root.$controller, 1);
                $controllerName = str_replace('/', '\\', $controllerName);
                return new $controllerName($cfg, $request);
            }
            $lastSlash = strrpos($controller, '/');
            $extraParam[] = substr($controller, $lastSlash + 1);
            $controller = substr($controller, 0, $lastSlash);
            $loops -= 1;
        }
        return null;
    }

    /**
     * Analyzes the HTTP request and retrieves the request information needed.
     * 
     * @return array  The request information.
     */
    function getRequest () {
        // Analyzes the request.
        $request = array(
            'from' => $_SERVER['REMOTE_ADDR'],
            'appName' => $_SERVER['SERVER_NAME'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'url' => $_SERVER['REQUEST_URI'],
            'controller' => $_REQUEST['controller'],
            'resource' => '',
            'data' => $_REQUEST
        );
        unset($request['data']['controller']);
        // If the server name is an IP address, uses the Computer Name instead.
        if (filter_var($request['appName'], FILTER_VALIDATE_IP)) {
            $request['appName'] = $_SERVER['COMPUTERNAME'];
        }
        // Returns the request.
        return $request;
    }
}