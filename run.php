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
// Shortcuts to the framework folders.
define('SYS', 'runPHP');
define('SYS_LOCALES', SYS.'/locales');
define('WEBAPPS', 'webapps');
define('APP', WEBAPPS.DIRECTORY_SEPARATOR.$_SERVER['SERVER_NAME']);
// Set auto-load and error handlers.
require(SYS.'/handlers.php');
// Load the I18N framework domain.
I18n::loadDomain('system', SYS_LOCALES);
// Session initialization.
session_name('rid');
session_start();

try {

    // Get and check the application configuration file.
    $cfg = parse_ini_file(APP.DIRECTORY_SEPARATOR.'app.cfg', true);
    if (!$cfg) {
        throw new RunException(500, __('The configuration file is not available.', 'system'), array(
            'code' => 'RPP-001',
            'configFile' => APP.DIRECTORY_SEPARATOR.'app.cfg',
            'helpLink' => 'http://runphp.taosmi.es/faq/rpp001'
        ));
    }

    // Shortcuts to the Web Application folders.
    define('APIS', APP.$cfg['PATHS']['apis']);
    define('APIS_PATH', $cfg['PATHS']['apis']);
    define('STATICS', APP.$cfg['PATHS']['statics']);
    define('PAGES', APP.$cfg['PATHS']['pages']);
    define('ERROR_PAGES', APP.$cfg['PATHS']['errorPages']);
    define('VIEWS_PATTERNS', APP.$cfg['PATHS']['viewsPatterns']);
    // Console flag and log configuration.
    define('CONSOLE', $cfg['LOGS']['console'] && array_key_exists('console', $_REQUEST));
    Logger::setLevel($cfg['LOGS']['logLevel']);
    // I18n configuration.
    I18n::setAutoLocale($cfg['I18N']['autolocale']);
    I18n::setDefaultLocale($cfg['I18N']['default']);
    I18n::loadDomain($cfg['I18N']['domain'], APP.$cfg['I18N']['path']);
    I18n::setDomain($cfg['I18N']['domain']);
    I18n::setLocale();

    // Get the request information.
    $request = Router::getRequest();
    Logger::sys(__('Request from "%s" to "%s%s", MIME type:"%s".', 'system'), $request['from'], $request['app'], $request['url'], $request['mime']);
    Logger::sys(__('Loading "%s" controller.', 'system'), $request['ctrl']);
    // If no request available, return a 404 error page.
    if (!$request['ctrl']) {
        throw new RunException(404, __('The page does not exist.', 'system'), array(
            'code' => 'RPP-00?',
            'url' => $request['url'],
            'helpLink' => 'http://runphp.taosmi.es/faq/rpp00?'
        ));
    }
    // Load and run a controller.
    $controller =  new $request['ctrlClass']($request, $cfg['REPOS']);
    $response = $controller->main($request['params']);
    if (!$response) {
        throw new RunException(500, __('No response is available from the server.'), array(
            'code' => 'RPP-002',
            'helpLink' => 'http://runphp.taosmi.es/faq/rpp002'
        ));
    }
    // Render the response.
    $response->render($request['mime']);

} catch (RunException $exception) {

    // Log the error exception.
    Logger::error($exception);
    // Use an HTML error page.
    $file = $exception->httpStatus === 404 ? 'notFoundError' : 'error';
    $path = file_exists(ERROR_PAGES.'/'.$file.'.php') ? ERROR_PAGES : SYS.'/html';
    // Create an error response.
    $response = new Response(array(
        'error' => array(
            'msg' => $exception->msg,
            'data' => $exception->data,
        )
    ), $exception->httpStatus);
    // Render the error.
    $response->setFile($path.DIRECTORY_SEPARATOR.$file);
    $response->render($request['mime']);
}

// Flush the log.
Logger::flush($cfg['LOGS']['path']);
// End the script.
exit();