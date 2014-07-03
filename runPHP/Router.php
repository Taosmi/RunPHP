<?php

namespace runPHP;

/**
 * Analyze the request to know which application and which controller are
 * involved to load and run them.
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
     * Show the system error as HTML. If the application has not an HTML error
     * page, show the default HTML framework error page.
     *
     * @param SystemException  $exception  An error exception.
     */
    public static function doSystemError ($exception) {
        // Log the error.
        Logger::error($exception);
        // Set the error page.
        header('HTTP/1.1 '.$exception->httpStatus);
        $appPath = APP.'/views/errors/';
        $errorPage = ($exception->httpStatus == 404) ? 'notFoundError.php' : 'error.php';
        // Show the application specific error page or the framework page.
        if (file_exists($appPath.$errorPage)) {
            include($appPath.$errorPage);
        } else {
            include(SYSTEM.'/html/'.$errorPage);
        }
    }

    /**
     * Show the error as HTML, JSON or XML. If the application has not an HTML
     * error page, show the default framework HTML error page.
     *
     * @param array           $request    The request information.
     * @param ErrorException  $exception  An error exception.
     */
    public static function doError ($request, $exception) {
        // Log the error.
        Logger::error($exception);
        // Set the error page.
        header('HTTP/1.1 '.$exception->httpStatus);
        if (!$request['controller']['format'] || $request['controller']['format'] === 'html') {
            $appError = '/views/errors/';
            $errorPage = ($exception->httpStatus == 404) ? 'notFoundError' : 'error';
            // Set the application specific error page or show the framework page.
            if (file_exists(APP.$appError.$errorPage.'.php')) {
                $source = $appError.$errorPage;
            } else {
                include(SYSTEM.'/html/'.$errorPage.'.php');
                return;
            }
        } else {
            $source = $exception;
        }
        // Render the error response.
        $errorResponse = new Response($source);
        $errorResponse->render($request['controller']['format']);
    }

    /**
     * Get the controller name involved with the request. If no controller is
     * available, returns null.
     *
     * @param  array   $request  A request information.
     * @return string            The controller name or null.
     * @throws                   ErrorException if no controller is found.
     */
    public static function getController ($request) {
        // Build the controller full class name.
        $controller = $request['cfg']['PATHS']['controllers'];
        if ($request['controller']['path'] != '/') {
            $controller.= $request['controller']['path'];
        }
        if (empty($request['controller']['name'])) {
            $controller.= '/index';
        } else {
            $controller.= '/'.$request['controller']['name'];
        }
        if (is_dir(APP.$controller)) {
            $controller.= '/index';
        }
        // Check the controller and get the class namespace.
        if (file_exists(APP.$controller.'.php')) {
            return str_replace('/', '\\', substr($controller, 1));
        }
        throw new ErrorException(__('Page not found', 'system'), null, 404);
    }

    /**
     * Analyze the HTTP request and retrieve the relevant request information.
     *
     * @return array  The request information.
     * @throws        SystemException if no application configuration is available.
     */
    public static function getRequest () {
        // Load the application configuration file path.
        $cfg = parse_ini_file(WEBAPPS.DIRECTORY_SEPARATOR.$_SERVER['SERVER_NAME'].DIRECTORY_SEPARATOR.'app.cfg', true);
        if (!$cfg) {
            throw new SystemException(__('The configuration file is not available.', 'system'), array(
                'code' => 'PPW-001',
                'configFile' => WEBAPPS.DIRECTORY_SEPARATOR.$_SERVER['SERVER_NAME'].DIRECTORY_SEPARATOR.'app.cfg',
                'helpLink' => 'http://runphp.taosmi.es/faq/ppw001'
            ));
        }
        // Remove the queryString from the URI and parse the URI.
        $url = pathinfo(str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
        // Console flag.
        define('CONSOLE', $cfg['LOGS']['console'] && array_key_exists('console', $_REQUEST));
        // Return the relevant request data.
        return array(
            'app' => $_SERVER['SERVER_NAME'],
            'cfg' => $cfg,
            'from' => $_SERVER['REMOTE_ADDR'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'url' => $_SERVER['REQUEST_URI'],
            'controller' => array(
                'path' => $url['dirname'],
                'name' => $url['filename'],
                'format' => $url['extension']
            )
        );
    }
}