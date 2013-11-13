<?php

namespace Taosmi\ProWeb\Core;


/**
 * This class provides all the methods needed to manage internationalization. 
 * It is based on gettext so you will need an external tool to provide the 
 * text files translations.
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
class I18n {

    /**
     * Sets a locale. If the locale is not available returns false, otherwise 
     * returns the current one.
     * 
     * @param locale  A string with the locale.
     * @return        A string with the current locale or false.
     */
    private static function set ($locale) {
        $newLocale = setlocale(LC_MESSAGES, $locale);
        if ($locale !== $newLocale) {
            Logger::sys('ATENTION! The locale "%s" is not available on the system', $locale);
            return false;
        }
        Logger::sys('The current locale is set to "%s"', $locale);
        return $newLocale;
    }

    /**
     * Gets and sets the locale from the 'language' cookie. If the locale is 
     * not available or the cookie doesn't exist returns false, otherwise 
     * returns the current locale.
     */
    private static function setLocaleFromCookie () {
        if ($_COOKIE['language']) {
            return self::set($_COOKIE['language']);
        }
        return false;
    }

    /**
     * Gets and sets the client browser main locale as the current one. If the 
     * locale is not available or the browser information is not recognized 
     * returns false, otherwise returns the current locale.
     * 
     * @return  A string with the current locale or false.
     */
    private static function setLocaleFromHTTP () {
        // Parses the HTTP ACCEPT LANGUAGE header.
        $format = '/([^;,]+)[^,]*/';
        $result = preg_match_all($format, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang);
        // Sets the main locale.
        if ($result) {
            $locale = split('-', current($lang[1]));
            $locale = $locale[0].'_'.strtoupper($locale[1]);
            return self::set($locale);
        }
        return false;
    }


    /**
     * Returns the current locale.
     * 
     * @return  A string with the current locale.
     */
    public static function getLocale () {
        return setlocale(LC_MESSAGES, 0);
    }

    /**
     * Loads a domain resource.
     * 
     * @param domain  A string with the domain name.
     * @param path    A string with the domain folder path.
     */
    public static function loadDomain ($domain, $path) {
        bindtextdomain($domain, $path);
    }

    /**
     * Sets a domain as the current one.
     * 
     * @param domain  A string with the domain name.
     */
    public static function setDomain ($domain) {
        textdomain($domain);
    }

    /**
     * Sets a locale as the current one. If no locale is specified, tries to 
     * get a locale either from the cookie, either from the browser, or either 
     * the default one, by this order. If no locale could be set returns false.
     * 
     * @param locale  A string with a locale.
     * @return        A string with the current locale or false.
     */
    public static function setLocale ($locale = null) {
        // Sets the locale from the argument.
        if ($locale) {
            Logger::sys('Setting the locale "%s"', $locale);
            return self::set($locale);
        }
        // If the auto-locale is set, tries to get the locale.
        // Gets the locale from the cookie.
        if (AUTO_LOCALE) {
            Logger::sys('Setting the locale from Cookie...');
            $locale = self::setLocaleFromCookie();
        }
        // Gets the locale from the HTTP request.
        if (AUTO_LOCALE && !$locale) {
            Logger::sys('Setting the locale from HTTP...');
            $locale = self::setLocaleFromHTTP();
        }
        // Gets the locale from the default configuration.
        if (DEFAULT_LOCALE && !$locale) {
            Logger::sys('Setting the locale from default...');
            $locale = self::set(DEFAULT_LOCALE);
        }
        if (!$locale) {
            Logger::sys('No locale has been set');
        }
        // Returns the current locale.
        return $locale;
    }
}

/**
 * Gets the value that matches the key provided from the current domain. If a 
 * domain is provided, it will override the current one.
 * 
 * @param key     A string with a key.
 * @param domain  A string with a domain name (optional).
 * @return        A string with the value for the key and domain.
 */
function __($key, $domain = null) {
    if ($domain) {
        return dgettext($domain, $key);
    }
    return gettext($key);
}

/**
 * Prints the value that matches the key provided from the current domain. If a 
 * domain is provided, it will override the current one.
 * 
 * @param key     A string with the key.
 * @param domain  A string with a domain name (optional).
 */
function _e($key, $domain = null) {
    echo __($key, $domain);
}
?>