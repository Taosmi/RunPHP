<?php

namespace runPHP;

/**
 * This class define a controller interface. A controller main function will be
 * executed when its path (relative to the webApp) matches the HTTP request URL.
 *
 * @author Miguel Angel Garcia
 *
 * Copyright 2014 TAOSMI Technology
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
interface IController {

    /**
     * The controller will get a reference to the request information.
     *
     * @param array  $request  The request information.
     */
    public function __construct ($request);

    /**
     * Abstract method to implement on any Controller. This method will be
     * executed by the framework just after the Controller is loaded.
     *
     * @return Response  A Response with the output data.
     */
    public function main ();

}