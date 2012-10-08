<?php
/**
 * This class is an Abstract class and must be extended to implement a Command 
 * Controller. It provides the basic functionality to get the request 
 * information, to redirect to another Command Controller and to load extra 
 * functionality by plug in extensions.
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
abstract class CommandController {

    /**
     * Abstract method to implement on any Command Controller.
     * This method will be executed by the framework just after the Command 
     * Controller is loaded.
     */
    abstract protected function main ();

    /**
     * The request object.
     */
    public $request;

    /**
     * The DAL (Data Access Layer) object.
     */
    public $DAL;

    /**
     * Creates a new command controller, loading the extensions and the helpers 
     * involved. All the command controllers gets a reference to the request 
     * object as the parameter that will be shared with the extensions.
     * 
     * @param request  a request object reference
     */
    public function __construct (&$request) {
        $this->request = $request;
        Console::logSys('Running the Command Controller '.$this->request->get('cmd'));
        // Loads the extensions.
        foreach ($this->request->get('cfg','EXTS') as $eName => $eFile) {
            $this->loadExtension($eFile, $eName, $this);
        }
        // Loads the Helpers.
        foreach ($this->request->get('cfg','HELPERS') as $helper) {
            include_once(HELPERS.$helper);
        }
    }

    /**
     * Initializes a DAL (Data Access Layer) module. The DAL module must be 
     * defined in the application configuration file.
     * 
     * @param DALmodule  a string with the name of the DAL module
     * @throws           SYSException('0300') if the DAL module is not defined 
     * @throws           SYSException('0301') if the DAL module is missing
    */
    public function loadDAL ($DALmodule) {
        $DALcfg = $this->request->get('cfg','DAL');
        // Gets the string configuration from the DAL module.
        if (!isset($DALcfg[$DALmodule])) {
            throw new SYSException('0300', array(
                'DALmodule' => $DALmodule
            ));
        }
        // Gets the DAL module name and the string configuration.
        list($DALname, $DALstring) = explode(',', $DALcfg[$DALmodule], 2);
        // Gets the DAL file name.
        $DALfile = DAL.'/'.$DALname.'.php';
        // If the DAL file is missing, throws an exception.
        if (!file_exists($DALfile)) {
            throw new SYSException('0301', array(
                'DALmodule' => $DALname,
                'DALfile' => $DALfile
            ));
        }
        // Creates a new DAL object.
        require_once($DALfile);
        $this->DAL = new $DALname($DALstring);
    }

    /**
     * Loads an extension. The extension name must be the extension file name 
     * starting with a / and without path and file extension (.php) and must be 
     * equal to the extension class name. If the plug name is already in use, 
     * no extension will be loaded.
     * 
     * @param extName   a string with the extension name
     * @param plugName  a string with the name the extension will be plug in
     * @param param     a reference to be used when creating the extension (optional)
     * @return          true if the extension is successfully loaded, otherwise false
     * @throws          SYSException(0302) if the extension is missing
     */
    public function loadExtension ($extName, $plugName, &$param = false) {
        // Checks if the plug name is available.
        if (isset($this->$plugName)) {
            return false;
        }
        // Loads the file extension.
        $extFile = EXTENSIONS.$extName.'.php';
        if (!file_exists($extFile)) {
            throw new SYSException('0302', array(
                'extName' => $extName,
                'file' => $extFile
            ));
        }
        // Plugs the extension.
        require_once($extFile);
        $extClass = basename($extName);
        $this->$plugName = new $extClass($param);
        return true;
    }

    /**
     * Redirects to another Command Controller.
     * 
     * @param to  a string with the /controller/action format
     */
    public function redirect ($to) {
        // Updates the console.
        Console::logSys('Redirected to '.$to);
        Console::flush();
        // Redirects the flow.
        header('Location: '.BASE_URL.$to);
        exit();
    }
}
?>