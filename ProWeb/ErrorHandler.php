<?php

/**
 * Error managing.
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
     * Shows an error page that matches the type error if available.
     * If no type error available shows the default error page.
     * If no default error page shows the framework error page.
     *
     * @param ErrorException $exception  An error exception.
     */
    function doError ($exception) {
        // Logs the error.
        Logger::error($exception);
        $errorPath = APP.'/views/errors/';
        $errorPage = $exception->type.'Error.php';
        if (file_exists($errorPath.$errorPage)) {
            // Shows the application specific error page.
            include($errorPath.$errorPage);
        } else if (file_exists($errorPath.'error.php')) {
            // Shows the application default error page.
            include($errorPath.'error.php');
        } else if (file_exists(SYSTEM.'/html/'.$exception->type.'Error.php')) {
            // Shows the system specific error page.
            include(SYSTEM.'/html/'.$exception->type.'Error.php');
        } else {
            // Shows the system fatal error page.
            include(SYSTEM.'/html/error.php');
        }
    }
}