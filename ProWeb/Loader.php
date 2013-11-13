<?php

namespace ProWeb;

 
/**
 * Auto loader for the core and controller classes.
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
class Loader {

    /**
     * Registers the auto-load functions.
     */
    public static function register () {
        spl_autoload_extensions('.php');
        spl_autoload_register('ProWeb\Loader::autoCore');
        spl_autoload_register('ProWeb\Loader::autoWebApp');
    }

    /**
     * Auto-load function for the core classes.
     * 
     * @param class  A string with the complete core class name.
     */
    private static function autoCore ($class) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 7)).'.php';
        include_once($class);
    }

    /**
     * Auto-load function for the controllers.
     *
     * @param class  A string with the complete controller class name.  
     */
    private static function autoWebApp ($class) {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, APP.'\\'.$class).'.php';
        include_once($class);
    }
}
?>