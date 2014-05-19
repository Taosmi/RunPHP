<?php

namespace proWeb;

/**
 * All the requests will hit this script, the Front Controller.
 * This script initializes the framework resources needed to run the PHProWeb.
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

// Framework path and Web applications path.
define('SYSTEM', 'proWeb');
define('WEBAPPS', 'webapps');
// Framework locales.
define('SYS_LOCALES', SYSTEM.'/locales');
// Sets the time zone to UTC.
date_default_timezone_set('UTC');

try {
    // Loads the PHProWeb Route functions.
    require(SYSTEM.'/Router.php');
    // Gets the request info, the application configuration and the system domain.
    $request = getRequest();
    $appCfg = getAppConfig($request);
    I18n::loadDomain('system', SYS_LOCALES);
    // Checks configuration file.
    if (empty($appCfg)) {
        throw new SystemException(__('There is no application configuration file.', 'system'), $request);
    }
    // Defines the Application path, the Resources path and the base HTTP URL.
    define('APP', WEBAPPS.DIRECTORY_SEPARATOR.$request['appName']);
    define('RESOURCES', APP.$appCfg['PATHS']['resources']);
    define('BASE_URL', 'http://'.$request['appName']);
    // Log and I18n configuration.
    Logger::setLevel($appCfg['LOGS']['logLevel']);
    I18n::setAutoLocale($appCfg['I18N']['autolocale']);
    I18n::setDefaultLocale($appCfg['I18N']['default']);
    // Loads the application domain and sets the locale.
    I18n::loadDomain($appCfg['I18N']['domain'], APP.$appCfg['I18N']['path']);
    I18n::setDomain($appCfg['I18N']['domain']);
    I18n::setLocale();
    // Loads and runs the Controller.
    Logger::sys(__('Request from %s to "%s/%s".', 'system'), $request['from'], $request['appName'], $request['controller']);
    $controllerName = getController($appCfg, $request);
    $request['controller'] = $controllerName;
    // Check if no controller was found for the HTTP request.
    if (!$controllerName) {
        throw new SystemException(__('Page not found.', 'system'), $request, 404);
    }
    $controller = new $controllerName($appCfg, $request);
} catch (ErrorException $exception) {
    doError($exception);
}

// Flushes the log.
Logger::flush($appCfg);
exit();