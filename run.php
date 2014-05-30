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

// Shortcuts to the RunPHP folder, the RunPHP locales folder and the Web applications folder.
define('SYSTEM', 'runPHP');
define('SYS_LOCALES', SYSTEM.'/locales');
define('WEBAPPS', 'webapps');

// Loader for undefined classes and load the I18N framework texts.
require(SYSTEM.'/Loader.php');
I18n::loadDomain('system', SYS_LOCALES);

try {

    // Get the request info.
    $request = Router::getRequest();

    // Shortcuts to the Web application folder and the static folder.
    define('APP', WEBAPPS.DIRECTORY_SEPARATOR.$request['app']);
    define('STATIC', APP.$request['cfg']['PATHS']['static']);

    // Log and I18n configuration.
    Logger::setLevel($request['cfg']['LOGS']['logLevel']);
    I18n::setAutoLocale($request['cfg']['I18N']['autolocale']);
    I18n::setDefaultLocale($request['cfg']['I18N']['default']);
    I18n::loadDomain($request['cfg']['I18N']['domain'], APP.$request['cfg']['I18N']['path']);
    I18n::setDomain($request['cfg']['I18N']['domain']);
    I18n::setLocale();

    // Load and run the controller.
    Logger::sys(__('Request from %s to "%s%s".', 'system'), $request['from'], $request['app'], $request['url']);
    $controllerName = Router::getController($request);
    $controller = new $controllerName($request);
    $response = $controller->main();
    $response->render($request['format']);

} catch (ErrorException $exception) {

    // Work In Progress.
    Router::doError($exception);

}

// Flushes the log.
Logger::flush($request['cfg']['LOGS']['path']);
exit();