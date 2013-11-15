<?php

namespace ProWeb;


/**
 * This class processes an error and shows a proper message to the user.
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
class ErrorHandler {

    public static function sysError ($request, $exception) {
        // Gets the error message.
        $logTxt = 'Code '.$exception->getMessage().': '.__($exception->getMessage(), 'system').'\n';
        $logTxt.= 'Program: '.basename($exception->getFile()). ' ('.$exception->getLine().')\n';
        foreach (array_keys($exception->data) as $key) {
            $logTxt.= $key.': '.$exception->data[$key].'\n';
        }
        // Logs the error and shows the error page.
        Logger::error($logTxt);
        include(SYSTEM.'/html/error.php');
    }

    public static function error404 ($exception) {
        // Gets the error message.
        $errorMsg = __($exception->getMessage(), 'system');
        // Logs the error.
        Logger::error('<<< 404 ERROR processing %s', $exception->getMessage());
        // Shows the error page.
        include(SYSTEM.'/html/error404.php');
    }

}
?>