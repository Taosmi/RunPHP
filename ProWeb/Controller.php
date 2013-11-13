<?php

namespace ProWeb;


/**
 * This class is an abstract class and must be extended to implement a Controller.
 * A controller runs when its path (relative to the webApp) matches the HTTP 
 * request URL. The controller decides what to do next. By default, it provides 
 * the functionality to load extensions and to redirect to another Controller.
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
abstract class Controller {

    /**
     * The application configuration and the request info.
     */
    public $cfg, $request;


    /**
     * Abstract method to implement on any Controller. This method will be 
     * executed by the framework just after the Controller is loaded.
     */
    abstract public function main ();


    /**
     * Loads the extensions involved. All the controllers get a reference to the 
     * application configuration and to the request information.
     * 
     * @param cfg      An associative array with an application configuration. 
     * @param request  An associative array with the request information.
     */
    public function __construct ($cfg, $request) {
        $this->cfg = $cfg;
        $this->request = $request;
        // Loads the extensions.
        Logger::sys('Initializing the Controller "%s"', $request['controller']);
        // Plugs the extensions.
        foreach ($cfg['EXTS'] as $extName => $extClass) {
            $this->loadExtension($extName, $extClass, $this);
        }
    }

    /**
     * Loads an extension. The extension name must be the extension file name 
     * starting with a slash (/) and without path and file extension (.php) and 
     * must be equal to the extension class name. If the plug name is already 
     * in use, no extension will be loaded.
     * 
     * @param extName   A string with the name the extension will be plug-in.
     * @param extClass  A string with the extension class name.
     * @param param     A reference to be used when creating the extension (optional).
     * @return          True if the extension is successfully loaded, otherwise false.
     * @throws          ErrorException(0004) if the extension name is already used.
     * @throws          ErrorException(0005) if the extension class is missing.
     */
    public function loadExtension ($extName, $extClass, $controller) {
        // Checks if the plug name is available.
        if (isset($this->$extName)) {
            throw new ErrorException('0004', array(
                'extName' => $extName
            ));
        }
        // Checks if the extension file exists.
        $extFile = str_replace('\\', DIRECTORY_SEPARATOR, $extClass).'.php';
        if (!file_exists($extFile)) {
            throw new ErrorException('0006', array(
                'extName' => $extName,
                'file' => $extFile
            ));
        }
        // Plugs the extension.
        $this->$extName = new $extClass($controller);
        // Loads the internationalization domain of the extension.
        //I18n::loadSysDomain(substr($extName, 1));
        return true;
    }

    /**
     * Redirects to another Command Controller. Must be used before sending or 
     * displaying any data.
     * 
     * @param to  A string with the /controller format.
     */
    public function redirect ($to) {
        // Updates the log.
        Logger::debug('<<< Redirecting to Controller %s', $to);
        Logger::flush($this->cfg);
        // Redirects the flow.
        header('Location: '.BASE_URL.$to);
        exit();
    }
}
?>