<?php
/**
 * This class provides the methods to manage a request: analyzing a request and 
 * loading the application configuration on creation time, loading the command 
 * controller and retrieving information about the request.
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
class Request {

    /**
     * The application configuration and the request information.
     */
    private $cfg, $request = array();

    /**
     * Analyzes and updates the request.
     * 
     * @throws  SYSException(0001) if the request does not belong to an application
     */
    private function analyze () {
        Console::logSys('Processing new request.');
        // Analyzes the request.
        if (isset($_SERVER['SERVER_NAME'])) {
            // HTTP request.
            $this->request['type'] = 'http';
            $this->request['appName'] = $_SERVER['SERVER_NAME'];
            $this->request['cmd'] = $_GET['command'] ? '/'.$_GET['command'] : '/index';
            $this->request['params'] = isset($_GET['params']) ? $_GET['params'] : null;
        } else {
            // Shell request.
            $this->request['type'] = 'shell';
            $this->request['appName'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : null;
            $this->request['cmd'] = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
            $this->request['params'] = isset($_SERVER['argv'][3]) ? $_SERVER['argv'][3] : null;
        }
        // If the server name is an IP, uses the Computer Name instead.
        if (filter_var($this->request['appName'], FILTER_VALIDATE_IP)) {
            $this->request['appName'] = $_SERVER['COMPUTERNAME'];
        }
        Console::logSys($this->request);
        // The request must belong to an application, otherwise throws an error.
        if (!$this->request['appName']) {
            throw new SYSException('0001');
        }
    }

    /**
     * Loads the application configuration file.
     * 
     * @throws  SYSException(0002) if the configuration file is not available
     */
    private function loadCfg () {
        // Defines the Application path and the base HTTP URL.
        define('APP', APPS.$this->request['appName']);
        define('BASE_URL', 'http://'.APP);
        // Loads the application configuration file.
        Console::logSys('Loading the configuration file for '.APP);
        if (!file_exists(APP.'/config.ini')) {
            throw new SYSException('0002', array(
                'appName' => $this->request['appName']
            ));
        }
        $this->cfg = parse_ini_file(APP.'/config.ini', true);
    }

    /**
     * Gets the command controller file path.
     */
    private function getCmdPath () {
        $this->request['cmdPath'] = APP.$this->cfg['PATHS']['controllers'].$this->request['cmd'];
        // If the Command Controller is a directory, assumes 'command + /index'.
        if (is_dir($this->request['cmdPath'])) {
            $this->request['cmd'].= '/index';
            $this->request['cmdPath'].= '/index';
        }
        $this->request['cmdPath'].= '.php';
    }


    /**
     * Creates a new request, involving the analysis, loading the configuration 
     * file and retrieving the command controller path.
     */
    public function __construct () {
        $this->analyze();
        $this->loadCfg();
        $this->getCmdPath();
    }

    /**
     * Gets the request information. If a type is provided, returns the value 
     * for that type of information or null if it is not defined. If no type, 
     * returns the entire request object. To get access to the application 
     * configuration provide 'cfg' as the type and then the option to retrieve. 
     * If no option, returns all the configuration information or null if it 
     * is not defined.
     * 
     * @param type    a string with the type of info to get from the request (optional)
     * @param option  a string with the configuration option to retrieve (optional)
     * @return        the information requested
     */
    public function get ($type, $option = null) {
        // If no type is provided, returns all the request info.
        if (!$type) {
            return $this->request;
        }
        switch ($type) {
            case 'cfg':
                if (!$option) {
                    return $this->cfg;
                }
                if (array_key_exists($option, $this->cfg)) {
                    return $this->cfg[$option];
                }
                break;
            default:
                if (array_key_exists($type, $this->request)) {
                    return $this->request[$type];
                }
        }
        return null;
    }

    /**
     * Loads the command controller which will handle the request.
     * 
     * @throws  SYSException(0404) if the command controller is not available
     * @return  a command controller
     */
    public function getCmd () {
        Console::logSys('Loading Command Controller file '.$this->request['cmdPath']);
        // Checks the command controller is available.
        if (!file_exists($this->request['cmdPath'])) {
            throw new SYSException('0404', array(
                'path' => $this->request['cmdPath']
            ));
        }
        // Loads the command controller.
        require($this->request['cmdPath']);
        $cmdName = basename($this->request['cmd']);
        return new $cmdName($this);
    }
}
?>