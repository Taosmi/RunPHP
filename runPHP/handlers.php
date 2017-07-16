<?php

namespace runPHP {

    /**
     * Function to be set as auto-load handler. Load the class from a file
     * that match the full class name (with namespace), adding the extension
     * '.php'.
     *
     * @param string  $class  A complete class name.
     */
    function loader($class) {
        // Replace the namespace separator (\) with the directory separator.
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
        if (file_exists($file)) {
            // Try to include the file from the root folder.
            include_once($file);
        } else if (file_exists(APP.DIRECTORY_SEPARATOR.$file)) {
            // Try to include the file from the web application folder.
            include_once(APP.DIRECTORY_SEPARATOR.$file);
        }
    }

    // Register the function as an auto-load handler.
    spl_autoload_register('\runPHP\loader');

    /**
     * Error function to set as error handler. Get the error information
     * and show an HTML page with it.
     *
     * @param  integer  $code  A code error.
     * @param  string   $msg   A message error.
     * @param  string   $file  A file were an error happened.
     * @param  integer  $line  A line were an error happened.
     * @param  array    $data  Context data of an error.
     */
    function errorHandler ($code, $msg, $file = null, $line = null, $data = null) {
        header('HTTP/1.1 500');
        $error = array(
            'msg' => __('Fatal Error!', 'system'),
            'data' => array(
                'code' => $code,
                'message' => $msg,
                'file' => $file,
                'line' => $line,
                'data' => $data
            )
        );
        require(SYS.'/html/error.php');
        exit();
    }

    // Register an error handler.
    set_error_handler('\runPHP\errorHandler', E_ALL | E_STRICT);
}