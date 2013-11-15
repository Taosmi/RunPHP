<?php

namespace ProWeb;


/**
 * This class analyzes the request to know which application and which 
 * controller are involved to load and run them.
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
class Router {

    /**
     * Loads the application configuration file involved in a request.
     * 
     * @param request  An associative array with a request information.
     * @return         An associative array with an application configuration.
     * @throws         ErrorException(0001) if the request does not belong to an application.
     * @throws         ErrorException(0002) if the configuration file is not available.
     * @throws         ErrorException(0003) if the log folder is not available.
     */
    public static function getAppConfig ($request) {
        // The request must belong to an application, otherwise throws an error.
        if (!$request['appName']) {
            throw new ErrorException('0001');
        }
        // Defines the Application path and the base HTTP URL.
        define('APP', WEBAPPS.DIRECTORY_SEPARATOR.$request['appName']);
        define('BASE_URL', 'http://'.$request['appName']);
        $configFile = APP.DIRECTORY_SEPARATOR.'config.ini';
        // Loads the application configuration file.
        Logger::sys('Loading the configuration file for "%s"', APP);
        if (!file_exists($configFile)) {
            throw new ErrorException('0002', array(
                'appName' => $request['appName']
            ));
        }
        $cfg = parse_ini_file($configFile, true);
        // Checks if the log folder exists.
        if (!is_dir(APP.$cfg['LOGS']['path'])) {
            throw new ErrorException('0003', array(
                'path' => APP.$cfg['LOGS']['path']
            ));
        }
        // Defines the application log configuration.
        switch ($cfg['LOGS']['logStatus']) {
            case '0': define('LOG_LEVEL', 0); break;
            case '1': define('LOG_LEVEL', 1); break;
            default: define('LOG_LEVEL', 2);
        }
        // Returns the configuration properties.
        return $cfg;
    }

    /**
     * Gets the controller file involved and returns a new instance of it. Once 
     * the request is analyzed, the framework knows which application and 
     * controller are involved.
     *
     * @param cfg      An associative array with an application configuration. 
     * @param request  An associative array with a request information.
     * @return         A controller object.
     */
    public static function getController ($cfg, $request) {
        // Creates and returns the controller.
        $controllerName = $cfg['PATHS']['controllers'].DIRECTORY_SEPARATOR.$request['controller'];
        $controllerFile = APP.$controllerName.'.php';
        // Checks if the controller exists.
        if (!file_exists($controllerFile)) {
            throw new Error404Exception($controllerFile);
        }
        // Converts the controller name to a name-space class.
        $controllerName = str_replace('/', '\\', $controllerName);
        return new $controllerName($cfg, $request);
    }

    /**
     * Analyzes and gets the request information.
     * 
     * @return  An associative array with the request information.
     */
    public static function getRequest () {
        // Analyzes the request.
        $request = array(
            'from' => $_SERVER['REMOTE_ADDR'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'appName' => $_SERVER['SERVER_NAME'],
            'controller' => $_GET['controller'],
            'resource' => $_GET['resource'],
            'postData' => $_POST,
            'getData' => $_GET
        );
        // If the server name is an IP address, uses the Computer Name instead.
        if (filter_var($request['appName'], FILTER_VALIDATE_IP)) {
            $request['appName'] = $_SERVER['COMPUTERNAME'];
        }
        // Updates the log and returns the request.
        Logger::sys('Request from %s to "%s/%s"', $request['from'], $request['appName'], $request['controller']);
        return $request;
    }
}
?>