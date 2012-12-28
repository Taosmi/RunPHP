<?php
/**
 * This class is a core extension. Implements the functionality to load a Model 
 * object. Once loaded, all the model business object will be available.
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
class eModel {

    /**
     * The Command Controller object reference.
     */
    private $cmd;

    /**
     * Remember that all the extensions will be initiated with a reference to 
     * the current command controller as unique parameter.
     * 
     * @param cmd  the command controller object reference
     */
    public function __construct (&$cmd) {
        $this->cmd = $cmd;
    }

    /**
     * Loads a Model object.
     * 
     * @param modelName  a string with the model name
     * @return           the requested model object
     * @throws           EXTException() if the Model does not exist
     */
    public function load ($modelName) {
        // Gets the model path from the application configuration.
        $paths = $this->cmd->request->get('cfg', 'PATHS');
        $modelPath = APP.$paths['models'];
        // Loads the model file.
        $modelFile = $modelPath.'/'.$modelName.'.php';
        Console::logSys('Loading Model ('.$modelFile.')');
        // If the model file is missing, throws an exception.
        if (!file_exists($modelFile)) {
            throw new EXTException('', array(
                'modelName' => $modelName,
                'modelFile' => $modelFile
            ));
        }
        // Creates the object model.
        require_once($modelFile);
        $model = new $modelName($this->cmd);
        // Returns the model.
        return $model;
    }
}

/**
 * This class is a container for business logic. It also adds functionality to 
 * load more models and to load another DAL (Data Access Layer) module.
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
class Model {

    /**
     * The Command Controller object reference.
     */
    private $cmd;

    /**
     * The DAL (Data Access Layer) object reference.
     */
    public $DAL;

    /**
     * Stores the Command Controller object reference.
     * 
     * @param cmd  the Command Controller object reference
     */
    public function __construct ($cmd) {
        $this->cmd = $cmd;
        $this->loadDAL();
    }

    /**
     * Loads a DAL (Data Access Layer) module. If no DAL module name is 
     * provided, loads the DAL module specified in the model class.
     */
    public function loadDAL ($DALcfg = null) {
        // If no DAL configuration, gets it from the model configuration if any.
        if (!$DALcfg && isset($this->DALcfg)) {
            $DALcfg = $this->DALcfg;
        }
        // Loads the DAL module and sets the target entity.
        if ($DALcfg) {
            $this->DAL =& $this->cmd->getDAL($DALcfg['module']);
            $this->DAL->from($DALcfg['entity']);
        }
    }

    /**
     * Loads a Model object.
     *
     * @param modelName  a string with the model name
     * @return           the requested model object
     * @throws           EXTException() if the Model does not exist
     */
    public function loadModel ($modelName) {
        // Gets the model path from the application configuration.
        $paths = $this->cmd->request->get('cfg', 'PATHS');
        $modelPath = APP.$paths['models'];
        // Loads the model file.
        $modelFile = $$modelPath.'/'.$modelName.'.php';
        // If the model file is missing, throws an exception.
        if (!file_exists($modelFile)) {
            throw new EXTException('', array(
                'modelName' => $modelName,
                'modelFile' => $modelFile
            ));
        }
        // Creates the object model.
        require_once($modelFile);
        $model = new $modelName($this->cmd);
        // Returns the model.
        return $model;
    }
}
?>