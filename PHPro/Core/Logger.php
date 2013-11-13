<?php

namespace Taosmi\ProWeb\Core;


/**
 * This class provides all the log methods needed to retrieve information about 
 * the framework's actions (persistent data access, errors, memory usage, system 
 * info, time consumed or warnings). This log methods must be placed wisely in 
 * your code in order to get a good debug information with them. All the 
 * framework's scripts are using them in key points to easy debug.
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
class Logger {

    /**
     * Log can be enabled, disabled or configured to only store errors.
     */
    public static $LOG_OFF = 0;
    public static $LOG_ON = 1;
    public static $LOG_ERRORS = 2;

    /**
     * Log data storage.
     */
    private static $logBuffer = array();

    /**
     * Stores the number of access to the persistent data.
     */
    private static $numPDA = 0;

    /**
     * Because of static nature of the class, the constructor is set as a 
     * private method to avoid incorrect use.
     */
    private function __construct () {}

    /**
     * Changes the unit of a file/memory size in bytes to a unit that fits 
     * better with that size number. The minimum size unit is byte and the 
     * maximum size unit is terabyte.
     * 
     * @param size  a number with a file/memory size in bytes
     * @return      a string with a file/memory size that fits better that size number
     */
    private static function getReadableSize ($size) {
        // Default number formats and available units measures.
        $numFormat = '%01.2f %s';
        $formats = array('bytes','kB','MB','GB','TB');
        // Calculates best unit for the number size.
        $unit = current($formats);
        while ($size > 1023) {
            $unit = next($formats);
            if (!$unit) {
                $unit = end($formats);
                break;
            };
            $size /= 1024;
        }
        // Bytes are not fractioned.
        if ($unit == 'bytes') {
            $numFormat = '%01d %s';
        }
        // Returns the formatted string.
        return sprintf($numFormat, $size, $unit);
    }

    /**
     * Changes the unit of an amount of time in seconds to a unit that fits 
     * better with that amount of time. The minimum time unit is miliseconds 
     * and the maximum time unit is minutes.
     * 
     * @param time  a float as an amount of time in seconds
     * @return      a string with a time scale that fits better that amount of time
     */
    private static function getReadableTime ($time) {
        if ($time < 1) {
            // If the time duration is less than a second, uses milliseconds.
            $format = '%.3f %s';
            $unit = 'msec';
            $time = $time * 1000;
        } else if ($time >= 1 && $time < 60) {
            // If the time duration is between 1 and 60 seconds, uses seconds.
            $unit = 'sec';
            $format = '%.3f %s';
        } else {
            // If the time duration is higher than 60 seconds, uses minutes.
            $format = '%.2f %s';
            $unit = 'min';
            $time = floor($time / 60) + ($time % 60) / 100;
        }
        // Returns the formatted string.
        return sprintf($format, $time, $unit);
    }

    /**
     * Interpolates the first string of the array with the other ones.
     * 
     * @param context  an array with strings to interpolate with
     */
    private static function interpolate ($context) {
        $msg = $context[0];
        unset($context[0]);
        if ($context) {
            $msg = vsprintf($msg, $context);
        }
        return $msg;
    }

    /**
     * Logs a message.
     * 
     * @param level  a string with the log level
     * @param msg    a string with the message
     */
    private static function log ($level, $msg) {
        // Logs the new entry.
        self::$logBuffer[] = array(
            'level' => $level,
            'date' => date('Y-m-d H:i:s'),
            'msg' => $msg
        );
     }


    /**
     * Logs a debug message. The extra parameters will be interpolated with 
     * the message.
     * 
     * @param msg  a string with a message
     */
    public static function debug ($msg) {
        // Checks the log configuration.
        if (LOG_LEVEL != Logger::$LOG_ON) {
            return false;
        }
        // Gets the message interpolated with the context.
        $msg = self::interpolate(func_get_args());
        // Logs the new debug entry.
        self::log('debug', $msg);
    }

    /**
     * Logs a message error. The extra parameters will be interpolated with 
     * the message.
     *
     * @param msg  a string with a message 
     */
    public static function error ($msg) {
        // Checks the log configuration.
        if (LOG_LEVEL == Logger::$LOG_OFF) {
            return false;
        }
        // Gets the message interpolated with the context.
        $msg = self::interpolate(func_get_args());
        // Logs the new error entry.
        self::log('error', $msg);
    }

    /**
     * Generates the log to the target depending on the type setting.
     * 
     * @param cfg  an associative array with an application configuration
     */
    public static function flush ($cfg) {
        // Opens the files.
        $appFilePath = APP.$cfg['LOGS']['path'].'/log'.date('Y.m.d').'.txt';
        $appFile = fopen($appFilePath, 'a');
        $sysFilePath = SYSLOG.'/log'.date('Y.m.d').'.txt';
        $sysFile = fopen($sysFilePath, 'a');
        // Writes the log file.
        $id = mt_rand();
        foreach (self::$logBuffer as $logItem) {
            if ($logItem['level'] === 'sys') {
                fwrite($sysFile, $logItem[date].' '.$id.' ['.APP.'] '.$logItem['msg']."\n");
            } else {
                fwrite($appFile, $logItem[date].' '.$id.' ['.$logItem['level'].'] '.$logItem['msg']."\n");
            }
        }
        // Closes the file.
        fclose($file);
    }

    /**
     * Retrieves the log data and statistics.
     * 
     * @return  an associative array with the log data and statistics
     */
    public static function getLog () {
        // Gets the request time.
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        // Returns the log info.
        return array(
            'time' => self::getReadableTime($time),
            'memory' => self::getReadableSize(memory_get_peak_usage()),
            'files' => get_included_files(),
            'pda' => self::$numPDA,
            'logs' => self::$logBuffer
        );
    }

    /**
     * Logs the memory usage of an object. If no object, logs the memory usage 
     * of the current script at this very moment.
     * 
     * @param object  a variable to measure (optional)
     */
    public static function memory ($msg, $object = null) {
        // Checks the log configuration.
        if (LOG_LEVEL != Logger::LOG_ON) {
            return false;
        }
        // Calculates the memory usage.
        $memory = ($object !== null) ? strlen(serialize($object)) : memory_get_usage();
        $msg.= ' '.self::getReadableSize($memory);
        // Logs the new memory entry.
        self::log('memory', $msg);
    }

    /**
     * Logs a persistent data access. If a start time is provided, calculates 
     * the gap between now and the start time.
     * 
     * @param msg        a string with a persistent data query
     * @param startTime  a float with the start UNIX timstamp of the query
     */
    public static function pda ($msg, $startTime = null) {
        // Checks the log configuration.
        if (LOG_LEVEL != Logger::LOG_ON) {
            return false;
        }
        // Gets the gap between the start time and the end time if available.
        if ($startTime) {
            $duration = microtime(true) - $startTime;
            $msg.= ' '.self::getReadableTime($duration);
        }
        // Logs the new PDA entry and uptades the pda count.
        self::log('pda', $msg);
        self::$numPDA += 1;
    }

    /**
     * Logs a message to the system log. The extra parameters will be 
     * interpolated with the message.
     * 
     * @param msg  a string with the message
     */
    public static function sys ($msg) {
        // Checks the log configuration.
        if (SYS_LOG_LEVEL != Logger::$LOG_ON) {
            return false;
        }
        // Gets the message interpolated with the context.
        $msg = self::interpolate(func_get_args());
        // Logs the new error entry.
        self::log('sys', $msg);
    }

    /**
     * Logs a message with the amount of seconds passed since the request 
     * arrived until this very moment. The extra parameters will be interpolated 
     * with the message.
     * 
     * @param msg  a string with a message
     */
    public static function time ($msg) {
        // Checks the log configuration.
        if (LOG_LEVEL != Logger::$LOG_ON) {
            return false;
        }
        // Gets the message interpolated with the context.
        $msg = self::interpolate(func_get_args());
        // Calculates the time gap.
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $msg.= ' '.self::getReadableTime($time);
        // Logs the new time entry.
        self::log('time', $msg);
    }

    /**
     * Logs a warning message. The extra parameters will be interpolated with 
     * the message.
     * 
     * @param msg  a string with a warning message
     */ 
    public static function warning ($msg) {
        // Checks the log configuration.
        if (LOG_LEVEL != Logger::$LOG_ON) {
            return false;
        }
        // Gets the message interpolated with the context.
        $msg = self::interpolate(func_get_args());
        // Logs the new warning entry.
        self::log('warning', $msg);
    }
}
?>