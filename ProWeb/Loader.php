<?php

/**
 * Registers functions for the auto-loading of classes when required.
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
namespace ProWeb {

    /**
     * Auto-load function for the core classes.
     *
     * Tries to load the class from a file at the PHProWeb folder, adding the
     * namespace path and the extension '.php' to the class name.
     *
     * @param string $class  The complete core class name.
     */
    function autoCore ($class) {
        // Seven is the length for 'ProWeb/'.
        $class = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 7)).'.php';
        include_once($class);
    }

    /**
     * Auto-load function for the controller classes.

     * Tries to load the class from a file at the WebApps folder, adding the
     * application name, the controller path and the extension '.php' to the
     * class name.
     *
     * @param string $class  The complete controller class name.
     */
    function autoWebApp ($class) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, APP.'\\'.$class).'.php';
        include_once($class);
    }

    // Registers the auto-load functions.
    spl_autoload_extensions('.php');
    spl_autoload_register('ProWeb\autoCore');
    spl_autoload_register('ProWeb\autoWebApp');
}