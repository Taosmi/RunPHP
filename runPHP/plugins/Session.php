<?php

namespace runPHP\plugins;
session_start();

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
     * Authorize the current session and store the authorization data. It will
     * replace the previous one if any and will regenerate the session Id.
     *
     * @param string  $user  An user Id.
     * @param array   $data  An associative array with more data (optional).
     */
    public static function authorize ($user, $data = null) {
        // Erase previous session data and regenerate the session ID.
        $_SESSION = array();
        session_regenerate_id(true);
        // Set the user MD5 encrypted finger print.
        $_SESSION['fingerprint'] = md5(self::getFingerPrint());
        // Set the session user data.
        $_SESSION['user'] = $user;
        $_SESSION['data'] = $data;
    }

   /**
     * Get the data of the current session with the provided key. If there is
     * no authorized session or the key does not exist, return null.
     * 
     * @param  string  $key  A key name.
     * @return array         The session data requested or null.
     */
    public static function get ($key) {
        if (array_key_exists($key, $_SESSION['data'])) {
            return $_SESSION['data'][$key];
        }
        return null;
    }

    /**
     * Check if the user is authorized.
     *
     * @return  boolean  True if the user is authorized, otherwise false.
     */
    public static function isAuthorized () {
        return ($_SESSION['fingerprint'] === md5(self::getFingerPrint()));
    }

    /**
     * Set a key value pair to the session.
     * 
     * @param string  $key    A key name to set on the session.
     * @param object  $value  The corresponding value.
     */
    public static function set ($key, $value) {
        $_SESSION['data'][$key] = $value;
    }

    /**
     * Destroy the current authorized session and the cookie session. This
     * method must be executed before any header is sent to the browser.
     */
    public static function unauthorized () {
        // Erase the session data.
        $_SESSION = array();
        // Erase the session cookie.
        if (isset($_COOKIE[session_name()])) {
            $ckData = session_get_cookie_params();
            setcookie(session_name(), '', -1, $ckData['path'], $ckData['domain'], $ckData['secure'], $ckData['httponly']);
        }
        // Destroy the session.
        session_destroy();
    }


    /**
     * Retrieve the finger print for the current user.
     *
     * @return string  The current finger print.
     */
    private static function getFingerPrint () {
        return APP.$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'].session_id();
    }
}