<?php

namespace ProWeb\Extensions;
use ProWeb;


/**
 * This class is a core extension. Implements the functionality to manage user 
 * authentication and session data storage.
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
class Session extends ProWeb\Extension {

    /**
     * The session name and the non encrypted session finger print.
     */
    private $sName, $uniqId;

    /**
     * The request type, needed to check for special shell access level.
     */
    private $requestType;


    /**
     * Initiates the extension. Starts the session and stores the session name 
     * and the request type.
     */
    public function init () {
        // Initiates the session.
        session_start();
        // Gets the session name and the request type.
        $this->sName = session_name();
        $this->requestType = $this->controller->request['method'];
        // Sets the user uniqId as the host name plus the visitor IP.
        $this->uniqId = php_uname('n').$_SERVER['REMOTE_ADDR'];
    }

    /**
     * Authorizes the current session and stores the authorization data. It 
     * will replace the previous one if any and regenerates the session Id.
     * 
     * @param user   A string with the user Id.
     * @param level  A number with the user access level (optional).
     * @param data   An associative array with more data (optional).
     */
    public function authorize ($user, $level = null, $data = null) {
        // Erases previous session data and regenerates the session ID.
        $_SESSION = array();
        session_regenerate_id();
        // Sets the user finger print as the uniqId MD5 encrypted.
        $_SESSION['fingerprint'] = md5($this->uniqId);
        // Sets the session user data.
        $_SESSION['user'] = $user;
        $_SESSION['level'] = $level;
        $_SESSION['data'] = $data;
    }

   /**
     * Gets the data of the current session with the provided key. If there is 
     * no authorized session or the key does not exist, returns null.
     * 
     * @param key  A string with a key name.
     * @return     The session data requested or null.
     */
    public function get ($key) {
        if (array_key_exists($key, $_SESSION['data'])) {
            return $_SESSION['data'][$key];
        }
        return null;
    }

    /**
     * Checks if the user is authorized. If an access level is provided, checks 
     * if the user meets that access level. The access level is just a number 
     * that represents the privilege of a user, the lower the more privileges 
     * the user have.
     * 
     * @param level  A number with an access level (optional).
     * @return       True if the user is authorized, otherwise false.
     */
    public function isAuthorized ($level = null) {
        // Checks if the finger print exists.
        if (!isset($_SESSION['fingerprint'])) {
            return false;
        }
        // Checks if the finger print is correct.
        if ($_SESSION['fingerprint'] !== md5($this->uniqId)) {
            return false;
        }
        // Checks if the user meets the access level required.
        if ($level !== null) {
            if (!isset($_SESSION['level'])) {
                return false;
            }
            // Checks special shell access level.
            if ($level === 0) {
                return ($this->requestType === 'shell');
            }
            return ($level >= $_SESSION['level']);
        }
        return true;
    }

    /**
     * Sets a key value pair to the session.
     * 
     * @param key    A string with a key name to set on the session.
     * @param value  The corresponding value.
     */
    public function set ($key, $value) {
        $_SESSION['data'][$key] = $value;
    }

    /**
     * Destroys the current authorized session and the cookie session. This 
     * method must be executed before any header is sent to the browser.
     */
    public function unauthorize () {
        // Erases the session data.
        $_SESSION = array();
        // Erases the session cookie.
        if (isset($_COOKIE[$this->sName])) {
            $ckData = session_get_cookie_params();
            setcookie($this->sName, '', -1, $ckData['path'], $ckData['domain'], $ckData['secure'], $ckData['httponly']);
        }
        // Destroys the session.
        session_destroy();
    }
}
?>