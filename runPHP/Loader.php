<?php

/**
 * Auto-load functions.
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
namespace runPHP {

    /**
     * Auto-load function for general purpose. Load the class from a file that
     * match the full class name (with namespace), adding the extension '.php'.
     *
     * @param string  $class  A complete class name.
     */
    function loader ($class) {
        // Replace the backslash (\) with the directory separator slash.
        include_once(str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php');
    }

    /**
     * Auto-load function for web application classes. Load the class from a
     * file that match the full class name (with namespace), adding the web
     * application path and the extension '.php'.
     *
     * @param string  $class  A complete web application class name.
     */
    function appLoader ($class) {
        // Replace the backslash (\) with the directory separator slash.
        include_once(APP.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php');
    }
}

namespace {

    // Register the auto-load functions.
    spl_autoload_register('runPHP\loader');
    spl_autoload_register('runPHP\appLoader');

}