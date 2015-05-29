<?php

namespace runPHP;

/**
 * All the requests will hit this script, the Front Controller. This script
 * initializes the RunPHP framework resources.
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

// Set the time zone to UTC.
date_default_timezone_set('UTC');

// Shortcuts to the RunPHP folders.
define('SYSTEM', 'runPHP');
define('SYS_LOCALES', SYSTEM.'/locales');
define('WEBAPPS', 'webapps');

// Class loader.
require(SYSTEM.'/Loader.php');
//  Load the framework I18N domain.
I18n::loadDomain('system', SYS_LOCALES);

try {

    // Get the request info and check the cfg file.
    $request = Router::getRequest();
    if (!$request['cfg']) {
        throw new ErrorException(__('The configuration file is not available.', 'system'), array(
            'code' => 'RPP-001',
            'configFile' => WEBAPPS.DIRECTORY_SEPARATOR.$_SERVER['SERVER_NAME'].DIRECTORY_SEPARATOR.'app.cfg',
            'helpLink' => 'http://runphp.taosmi.es/faq/rpp001'
        ));
    }

    // Shortcuts to the Web application folders and the console flag.
    define('APP', WEBAPPS.DIRECTORY_SEPARATOR.$request['app']);
    define('STATICS', APP.$request['cfg']['PATHS']['statics']);
    define('VIEWS', APP.$request['cfg']['PATHS']['views']);
    define('VIEWS_ERRORS', APP.$request['cfg']['PATHS']['viewsErrors']);
    define('VIEWS_TEMPLATES', APP.$request['cfg']['PATHS']['viewsTemplates']);
    define('CONSOLE', $request['cfg']['LOGS']['console'] && array_key_exists('console', $_REQUEST));

    // Log configuration.
    Logger::setLevel($request['cfg']['LOGS']['logLevel']);
    // I18n configuration.
    I18n::setAutoLocale($request['cfg']['I18N']['autolocale']);
    I18n::setDefaultLocale($request['cfg']['I18N']['default']);
    I18n::loadDomain($request['cfg']['I18N']['domain'], APP.$request['cfg']['I18N']['path']);
    I18n::setDomain($request['cfg']['I18N']['domain']);
    I18n::setLocale();

    // Load the controller.
    Logger::sys(__('Request from %s to "%s%s".', 'system'), $request['from'], $request['app'], $request['url']);
    $controllerName = Router::getController($request);
    if ($controllerName) {
        // Get a specific controller.
        $controller = new $controllerName($request);
    } else {
        // Get a default controller.
        require(SYSTEM.'/default/defaultController.php');
        $controller = new defaultController($request);
    }
    // Run the controller.
    $response = $controller->main();
    if (!$response) {
        throw new ErrorException(__('No response is available from the server.'), array(
            'code' => 'RPP-00x',
            'helpLink' => 'http://runphp.taosmi.es/faq/rpp00x'
        ), 500);
    }

} catch (ErrorException $exception) {

    // Handle an error.
    Logger::error($exception);
    // Handle an error.
    $response = new Response('html', array(
        'error' => array(
            'msg' => $exception->msg,
            'data' => $exception->data,
        )
    ), $exception->httpStatus);

}

// Render the response.
$response->render($request['controller']);
// Flush the log.
Logger::flush($request['cfg']['LOGS']['path']);
// End the script.
exit();