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
    // Checks request and configuration.
    if (empty($appCfg)) {
        throw new ErrorException('PPW-000', __('There is no application configuration file.', 'system'), $request, 'system');
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
    $controller = getController($appCfg, $request);
    // No controller found for the HTTP request.
    if (!$controller) {
        throw new ErrorException('PPW-404', null, $request, 'notFound');
    }
    $controller->main();
} catch (ErrorException $exception) {
    // Handles the error as properly as possible.
    doError($exception);
}

// Shows the console and flushes the log.
if (!empty($appCfg) && !empty($request)) {
    if ($appCfg['LOGS']['console'] && array_key_exists('console', $request['data'])) {
        require(SYSTEM.'/html/console.php');
    }
    Logger::flush($appCfg);
}

exit();