<?php

namespace ProWeb {

    /**
     * This class provides all the methods needed to manage i18n.
     *
     * It is based on 'gettext' so you will need an external tool to provide the
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
         * The auto-locale mode. By default false.
         */
        private static $autolocale = false;

        /**
         * The default locale.
         */
        private static $defaultLocale = 'es_US';


        /**
         * Sets a locale as the current one.
         *
         * If the locale is not available returns false, otherwise returns the
         * current one.
         *
         * @param string $locale  A locale.
         * @return string         The current locale or an empty string.
         */
        private static function set ($locale) {
            $newLocale = setlocale(LC_MESSAGES, $locale);
            if ($locale !== $newLocale) {
                Logger::sys(__('ATTENTION! The locale "%s" is not available on the system.', 'System'), $locale);
                return '';
            }
            Logger::sys(__('The current locale is set to "%s".', 'System'), $locale);
            return $newLocale;
        }

        /**
         * Gets and sets the locale from the language cookie.
         *
         * If the locale is not available or the cookie does not exist returns
         * an empty string, otherwise returns the current locale.
         *
         * @return string  The current locale or an empty string.
         */
        private static function setLocaleFromCookie () {
            if ($_COOKIE['language']) {
                return self::set($_COOKIE['language']);
            }
            return '';
        }

        /**
         * Gets and sets the client browser main locale as the current one.
         *
         * If the locale is not available or the browser information is not
         * recognized returns an empty string, otherwise returns the current
         * locale.
         *
         * @return string  The current locale or an empty string.
         */
        private static function setLocaleFromHTTP () {
            // Parses the HTTP ACCEPT LANGUAGE header.
            $format = '/([^;,]+)[^,]*/';
            $result = preg_match_all($format, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang);
            // Sets the main locale.
            if ($result) {
                $locale = explode('-', current($lang[1]));
                $locale = $locale[0].'_'.strtoupper($locale[1]);
                return self::set($locale);
            }
            return '';
        }


        /**
         * Returns the current locale.
         *
         * @return string  The current locale.
         */
        public static function getLocale () {
            return setlocale(LC_MESSAGES, 0);
        }

        /**
         * Loads a domain resource.
         *
         * @param string $domain  A domain name.
         * @param string $path    A domain folder path.
         */
        public static function loadDomain ($domain, $path) {
            bindtextdomain($domain, $path);
        }

        /**
         * Sets the auto-locale mode enabled or disabled.
         *
         * @param boolean $auto  The auto-locale mode.
         */
        public static function setAutoLocale ($auto) {
            self::$autolocale = $auto;
        }

        /**
         * Sets the default locale.
         *
         * @param string $locale  A locale to use as default.
         */
        public static function setDefaultLocale ($locale) {
            self::$defaultLocale = $locale;
        }

        /**
         * Sets a domain as the current one.
         *
         * @param string $domain  A domain name.
         */
        public static function setDomain ($domain) {
            textdomain($domain);
        }

        /**
         * Sets a locale as the current one.
         *
         * If no locale is specified, tries to get a locale either from the
         * cookie, either from the browser, or either the default one, by this
         * order. If no locale could be set returns false.
         *
         * @param string $locale  A locale.
         * @return string         The current locale or an empty string.
         */
        public static function setLocale ($locale = null) {
            // Sets the locale from the argument.
            if ($locale) {
                return self::set($locale);
            }
            // If no argument and the auto-locale is set, tries to get it.
            // Gets the locale from the cookie.
            if (self::$autolocale) {
                $locale = self::setLocaleFromCookie();
            }
            // Gets the locale from the HTTP request.
            if (self::$autolocale && !$locale) {
                $locale = self::setLocaleFromHTTP();
            }
            // Gets the locale from the default configuration.
            if (self::$defaultLocale && !$locale) {
                $locale = self::set(self::$defaultLocale);
            }
            if (!$locale) {
                Logger::sys(__('No locale has been set.', 'System'));
            }
            // Returns the current locale.
            return $locale;
        }
    }
}


namespace {

    /**
     * Gets the text that matches the key provided. If a domain is provided, it
     * will override the current one.
     *
     * @param string $key     A key.
     * @param string $domain  A domain name (optional).
     * @return string         The text for the key and domain.
     */
    function __($key, $domain = null) {
        if ($domain) {
            return dgettext($domain, $key);
        }
        return gettext($key);
    }

    /**
     * Prints the value that matches the key provided. If a domain is provided,
     * it will override the current one.
     *
     * @param string $key     A key.
     * @param string $domain  A domain name (optional).
     */
    function _e($key, $domain = null) {
        echo __($key, $domain);
    }
}