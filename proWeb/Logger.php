<?php

namespace ProWeb;

/**
 * This class provides all the log methods needed to log all kind of events.
 *
 * Repository access, errors, memory usage, system info, time consumed or
 * warnings can be log but only if this methods are placed wisely in your code.
 * All the framework's scripts are using them in key points to easy debug.
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
class Logger {

    /**
     * The log can be disabled (0), enabled(1) or configured to only store
     * errors (2).
     */
    private static $LOG_OFF = '0';
    private static $LOG_ON = '1';

    /**
     * The current log level. By default 2 (only errors).
     */
    private static $level = 2;

    /**
     * Log data buffer.
     */
    private static $logBuffer = array();

    /**
     * Number of access to the persistent data.
     */
    private static $numRepoAccess = 0;


    /**
     * Logs a debug message.
     * The extra parameters will be interpolated with the message. Logged only
     * when LOG_ON.
     *
     * @param string $msg  A message.
     */
    public static function debug ($msg) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Gets the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Logs the new debug entry.
            self::log('debug', $msg);
        }
    }

    /**
     * Logs an exception error.
     * Logged only when LOG_ERRORS.
     *
     * @param ErrorException $exception  An error exception object.
     */
    public static function error ($exception = null) {
        // Checks the log configuration.
        if (self::$level != Logger::$LOG_OFF) {
            // Logs the error entries.
            self::log('error', 'Code '.$exception->getCode().': '.$exception->getMessage());
            self::log('error', 'Program: '.basename($exception->getFile()). ' ('.$exception->getLine().')');
            if (!empty($exception->data)) {
                $logTxt = '';
                foreach (array_keys($exception->data) as $key) {
                    $logTxt.= $key.': '.$exception->data[$key].', ';
                }
                self::log('error', $logTxt);
            }
        }
    }

    /**
     * Stores the logs to the file.
     * 
     * @param array $cfg  The application configuration.
     */
    public static function flush ($cfg) {
        // Opens the files.
        $appFilePath = APP.$cfg['LOGS']['path'].'/log'.date('Y.m.d').'.txt';
        $appFile = fopen($appFilePath, 'a');
        // The id that identifies the logs from this request.
        $id = mt_rand(0, 10000);
        // Writes the log files.
        foreach (self::$logBuffer as $logItem) {
            fwrite($appFile, sprintf("%s (%05d) [%s] %7s - %s\n", $logItem['date'], $id, APP, $logItem['level'], $logItem['msg']));
        }
        // Closes the file.
        fclose($appFile);
    }

    /**
     * Retrieves the log data and statistics.
     * When this method is called, it is assumed that moment as the end time.
     * 
     * @return array  The log data and statistics.
     */
    public static function getLog () {
        // Gets the request time.
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        // Returns the log info.
        return array(
            'time' => self::getReadableTime($time),
            'memory' => self::getReadableSize(memory_get_peak_usage()),
            'files' => get_included_files(),
            'repo' => self::$numRepoAccess,
            'logs' => self::$logBuffer
        );
    }

    /**
     * Logs the memory usage of an object.
     * If no object, logs the memory usage of the current script at this very
     * moment. Logged only when LOG_ON.
     *
     * @param string $msg     A message.
     * @param object $object  A variable to measure (optional).
     */
    public static function memory ($msg, $object = null) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Calculates the memory usage.
            $memory = ($object !== null) ? strlen(serialize($object)) : memory_get_usage();
            $msg.= ' ('.self::getReadableSize($memory).')';
            // Logs the new memory entry.
            self::log('memory', $msg);
        }
    }

    /**
     * Logs a repository access.
     * If a start time is provided, calculates the gap between now and the start
     * time. Logged only when LOG_ON.
     *
     * @param string $msg        A repository query.
     * @param float  $startTime  The start UNIX time-stamp of the query (optional).
     */
    public static function repo ($msg, $startTime = null) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Gets the gap between the start time and the end time.
            if ($startTime) {
                $duration = microtime(true) - $startTime;
                $msg.= ' ('.self::getReadableTime($duration).')';
            }
            // Logs the new repository access and updates the counter.
            self::log('repo', $msg);
            self::$numRepoAccess += 1;
        }
    }

    /**
     * Sets a new log level.
     * The log level should be 0 (off), 1 (on) or 2 (only errors).
     *
     * @param int $level  A new log level.
     */
    public static function setLevel ($level) {
        self::$level = $level;
    }

    /**
     * Logs a message to the system log.
     * The extra parameters will be interpolated with the message. Logged only
     * when LOG_ON.
     *
     * @param string $msg  A message.
     */
    public static function sys ($msg) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Gets the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Logs the new error entry.
            self::log('system', $msg);
            }
    }

    /**
     * Logs a message with the amount of seconds passed since the request
     * arrived at the server until this very moment.
     * The extra parameters will be interpolated with the message. Logged only
     * when LOG_ON.
     *
     * @param string $msg  A message.
     */
    public static function time ($msg) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Gets the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Calculates the time gap.
            $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $msg.= ' ('.self::getReadableTime($time).')';
            // Logs the new time entry.
            self::log('time', $msg);
        }
    }

    /**
     * Logs a warning message.
     * The extra parameters will be interpolated with the message. Logged only
     * when LOG_ON.
     *
     * @param string $msg  A warning message.
     */ 
    public static function warning ($msg) {
        // Checks the log configuration.
        if (self::$level === Logger::$LOG_ON) {
            // Gets the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Logs the new warning entry.
            self::log('warning', $msg);
        }
    }


    /**
     * Changes the unit of a file/memory size in bytes to a unit that fits 
     * better with that size number. The minimum size unit is byte and the 
     * maximum size unit is terabyte.
     * 
     * @param int $size  A file/memory size in bytes.
     * @return string    A file/memory size that fits better that size number.
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
        // Bytes are not fractionated.
        if ($unit == 'bytes') {
            $numFormat = '%01d %s';
        }
        // Returns the formatted string.
        return sprintf($numFormat, $size, $unit);
    }

    /**
     * Changes the unit of an amount of time in seconds to a unit that fits 
     * better with that amount of time. The minimum time unit is milliseconds 
     * and the maximum time unit is minutes.
     * 
     * @param float $time  An amount of time in seconds.
     * @return string      A time that fits better that amount of time.
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
     * @param array $context  Strings to interpolate with.
     * @return string         The first string interpolated with the others.
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
     * @param string $level  A log level.
     * @param string $msg    A message.
     */
    private static function log ($level, $msg) {
        // Logs the new entry.
        self::$logBuffer[] = array(
            'level' => $level,
            'date' => date('Y-m-d H:i:s'),
            'msg' => $msg
        );
     }
}