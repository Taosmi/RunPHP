<?php
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
     * @param locale  a string with the locale
     * @return        a string with the current locale or false
     */
    private static function set ($locale) {
        $newLocale = setlocale(LC_MESSAGES, $locale);
        if ($locale !== $newLocale) {
            Console::logWarning('The locale \''.$locale.'\' is not available on the system');
            return false;
        }
        Console::logSys('The current locale is set to \''.$locale.'\'');
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
     * @return  a string with the current locale or false
     */
    private static function setLocaleFromHTTP () {
        // Parses the HTTP ACCEPT LANGUAGE header.
        $format = '/([^;,]+)[^,]*/';
        echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];
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
     * @return a string with the current locale
     */
    public static function getLocale () {
        return setlocale(LC_MESSAGES, 0);
    }

    /**
     * Loads a domain resource.
     * 
     * @param domain  a string with the domain name
     */
    public static function loadDomain ($domain) {
        global $request;
        $paths = $request->get('cfg', 'PATHS');
        bindtextdomain($domain, APP.$paths['locales']);
    }

    /**
     * Loads a system domain resource. This method must be used only by the 
     * framework to load the system and extension domains.
     * 
     * @param domain  a string with the system domain name
     */
    public static function loadSysDomain ($domain) {
        bindtextdomain($domain, LOCALES);
    }

    /**
     * Sets a domain as the current one.
     * 
     * @param domain  a string with the domain name
     */
    public static function setDomain ($domain) {
        textdomain($domain);
    }

    /**
     * Sets a locale as the current one. If no locale is specified, tries to 
     * get a locale either from the cookie, either from the browser, or either 
     * the default one, by this order. If no locale could be set returns false.
     * 
     * @param locale  a string with a locale
     * @return        a string with the current locale or false
     */
    public static function setLocale ($locale = null) {
        // Sets the locale from the argument.
        if ($locale) {
            Console::logSys('Setting the locale \''.$locale.'\'...');
            return self::set($locale);
        }
        // If the argument is null, tries to get the locale.
        global $request;
        $i18n = $request->get('cfg', 'I18N');
        // Gets the locale from the cookie.
        if ($i18n['fromCookie']) {
            Console::logSys('Setting the locale from Cookie...');
            $locale = self::setLocaleFromCookie();
        }
        // Gets the locale from the HTTP request.
        if (!$locale && $i18n['fromHTTP']) {
            Console::logSys('Setting the locale from HTTP...');
            $locale = self::setLocaleFromHTTP();
        }
        // Gets the locale from the default configuration.
        if (!$locale && $i18n['default']) {
            Console::logSys('Setting the locale from default configuration...');
            $locale = self::set($i18n['default']);
        }
        if (!$locale) {
            Console::logSys('No locale has been set');
        }
        // Returns the current locale.
        return $locale;
    }
}

/**
 * Gets the value that matches the key provided from the current domain. If a 
 * domain is provided, it will override the current one.
 * 
 * @param key     a string with a key
 * @param domain  a string with a domain name (optional)
 * @return        a string with the value for the key and domain
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
 * @param key     a string with the key
 * @param domain  a string with a domain name (optional)
 */
function _e($key, $domain = null) {
    echo __($key, $domain);
}
?>