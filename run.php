<?php

namespace ProWeb;


/**
 * All the requests will hit this script, the Front Controller.
 * This script initializes the framework resources needed to run ProWeb.
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

// Framework path.
define('SYSTEM', 'ProWeb');
// Web applications path.
define('WEBAPPS', 'webapps');

// Loads the PHPro class auto-loader.
require(SYSTEM.'/Loader.php');
// Registers the auto-loader.
Loader::register();

// Sets the locale from a Cookie or from the request or from default.
define('AUTO_LOCALE', true);
// Default locale.
define('DEFAULT_LOCALE', 'en_US');
// Framework locals.
define('SYS_LOCALES', SYSTEM.'/locales');

// System log level.
define('SYS_LOG_LEVEL', Logger::$LOG_ON);
// System log file.
define('SYSLOG', SYSTEM.'/logs');

// Sets the time zone to UTC.
date_default_timezone_set('UTC');

try {
    // Gets the locale and sets the Framework locale.
    I18n::setLocale();
    I18n::loadDomain('system', SYS_LOCALES);
    // Gets the request info and the application configuration.
    $request = Router::getRequest();
    $appCfg = Router::getAppConfig($request);
    // Application locale.
    I18n::setDomain($appCfg['I18N']['domain']);
    I18n::loadDomain($appCfg['I18N']['domain'], APP.$appCfg['I18N']['path']);
    // Loads and runs the Controller.
    $controller = Router::getController($appCfg, $request);
    $controller->main();
} catch (Error404Exception $exception) {
    ErrorHandler::error404($exception);
} catch (ErrorException $exception) {
    ErrorHandler::sysError($request, $exception);
}

// Shows the console and flushes the log.
if ($appCfg['LOGS']['console'] && array_key_exists('console', $request['data'])) {
    $console = Logger::getLog();
    require(SYSTEM.'/html/console.php');
}
Logger::flush($appCfg);

exit();
?>