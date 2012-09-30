<?php
/**
 * Once the HTTP Server is configured, all the request will hit this script, 
 * the Framework start point. This script sets the default constants and 
 * initiates the FrontController module (core module of the framework).
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
 * Path Constants.
 */
// Applications path.
define('APPS', '');
// PHPro path.
define('SYSTEM', '_PHPro');
// Extensions path.
define('EXTENSIONS', SYSTEM . '/extensions');
// DAL (Data Access Layer) path.
define('DAL', EXTENSIONS . '/dal');
// Helpers path.
define('HELPERS', SYSTEM . '/helpers');
// System logs path.
define('SYS_LOG', SYSTEM . '/logs');

// Sets the time zone to UTC.
date_default_timezone_set('UTC');

// Loads the Front Controller.
require (SYSTEM . '/frontController.php');
?>