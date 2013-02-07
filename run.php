<?php
/**
 * Once the HTTP Server is configured, all the request will hit this script, 
 * the Front Controller. This script sets the default constants, creates a 
 * request object, initiates and runs a command controller.
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

/**
 * Framework settings.
 */
// Applications path.
define('APPS', '');
// PHPro path.
define('SYSTEM', '_PHPro');
// Framework internal locales path.
define('LOCALES', SYSTEM.'/locales');
// Extensions path.
define('EXTENSIONS', SYSTEM.'/extensions');
// Helpers path.
define('HELPERS', SYSTEM.'/helpers');
// System logs path.
define('SYS_LOG', SYSTEM.'/logs');
// Display console.
define('CONSOLE', true);

// Sets the time zone to UTC.
date_default_timezone_set('UTC');

// Loads the Logger class.
require(SYSTEM.'/Logger.php');
// Loads the Error Handler class.
require(SYSTEM.'/ErrorHandler.php');
// Loads the Request class.
require(SYSTEM.'/Request.php');
// Loads the i18n class.
require(SYSTEM.'/I18n.php');

try {
    Logger::logSys('Processing new request.');
    // Gets the request object with all the information.
    $request = new Request();
    // Loads the internationalization domains.
    I18n::loadSysDomain('system');
    I18n::loadDomain('messages');
    I18n::setDomain('messages');
    // Loads and runs the main Command Controller.
    $cmd = $request->get('cmdObj');
    $cmd->main();
    // Flushes the console buffer.
    Logger::flush();
} catch (SYSException $exception) {
    // Handles a system exception.
    ErrorHandler::sysError($exception, $request);
} catch (EXTException $exception) {
    // Handles an Extension exception.
    ErrorHandler::extError($exception, $request);
}

exit();
?>