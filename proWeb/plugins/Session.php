<?php

namespace proWeb\plugins;

/**
 * This class implements the functionality to manage user authentication and
 * session data storage.
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
class Session {

    /**
     * The session name and the non encrypted session fingerprint.
     */
    private $sName, $fingerPrint;


    /**
     * Initiates the extension. Starts the session and stores the session name
     * and the request type. Also generates the fingerprint for the session as
     * the application name plus the User-Agent plus the visitor IP plus the
     * session id.
     */
    public function init () {
        // Initiates the session.
        session_start();
        // Gets the session name and the request type.
        $this->sName = session_name();
        // Sets the user fingerprint.
        $this->fingerPrint = APP.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].session_id();
    }

    /**
     * Authorizes the current session and stores the authorization data. It 
     * will replace the previous one if any and regenerates the session Id.
     * 
     * @param string $user  An user Id.
     * @param array  $data  An associative array with more data (optional).
     */
    public function authorize ($user, $data = null) {
        // Erases previous session data and regenerates the session ID.
        $_SESSION = array();
        session_regenerate_id(true);
        // Sets the user finger print MD5 encrypted.
        $_SESSION['fingerprint'] = md5($this->fingerPrint);
        // Sets the session user data.
        $_SESSION['user'] = $user;
        $_SESSION['data'] = $data;
    }

   /**
     * Gets the data of the current session with the provided key. If there is 
     * no authorized session or the key does not exist, returns null.
     * 
     * @param string $key  A key name.
     * @return array       The session data requested or null.
     */
    public function get ($key) {
        if (array_key_exists($key, $_SESSION['data'])) {
            return $_SESSION['data'][$key];
        }
        return null;
    }

    /**
     * Checks if the user is authorized.
     *
     * @return boolean    True if the user is authorized, otherwise false.
     */
    public function isAuthorized () {
        // Checks if the finger print is correct.
        if ($_SESSION['fingerprint'] === md5($this->fingerPrint)) {
            return true;
        }
        return false;
    }

    /**
     * Sets a key value pair to the session.
     * 
     * @param string $key    A key name to set on the session.
     * @param object $value  The corresponding value.
     */
    public function set ($key, $value) {
        $_SESSION['data'][$key] = $value;
    }

    /**
     * Destroys the current authorized session and the cookie session. This 
     * method must be executed before any header is sent to the browser.
     */
    public function unauthorized () {
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