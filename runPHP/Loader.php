<?php

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