<?php

namespace runPHP;

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
     * @var int  Log levels available.
     */
    const NONE = 0, ERROR = 1, WARNING = 3, DEBUG = 7, ALL = 127;

    /**
     * @var int  Specific log messages.
     */
    const SYS = 8, MEMO = 16, TIME = 32, REPO = 64;

    /**
     * @var string  The current log level. By default show only the errors.
     */
    private static $level = self::ERROR;

    /**
     * @var array  Log data buffer.
     */
    private static $logBuffer = array();

    /**
     * @var int  Number of access to the persistent data.
     */
    private static $numRepoAccess = 0;


    /**
     * Log a debug message. The extra parameters will be interpolated with the
     * message. Logged only when DEBUG level is set.
     *
     * @param string  $msg  A message.
     */
    public static function debug ($msg) {
        // Check the log configuration.
        if (self::$level & 4) {
            // Get the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Log the new debug entry.
            self::log('debug', $msg);
        }
    }

    /**
     * Log an exception error. Logged only when ERROR, WARNING or DEBUG level
     * are set.
     *
     * @param RunException  $exception  An error exception object.
     */
    public static function error ($exception = null) {
        // Check the log configuration.
        if (self::$level & 1) {
            // Log the error entries.
            self::log('error', $exception->msg);
            self::log('error', 'Program: '.basename($exception->getFile()). ' ('.$exception->getLine().')');
            if (!empty($exception->data)) {
                $logTxt = '';
                foreach (array_keys($exception->data) as $key) {
                    $logTxt.= $key.': "'.$exception->data[$key].'", ';
                }
                self::log('error', $logTxt);
            }
        }
    }

    /**
     * Store the logs to a file.
     *
     * @param string  $path  An application log folder.
     */
    public static function flush ($path) {
        // Write log file if the path exist.
        if (is_dir(APP.$path)) {
            // Open file.
            $appFilePath = APP.$path.'/log'.date('Y.m.d').'.txt';
            $appFile = fopen($appFilePath, 'a');
            // An ID that identifies all the logs from one request.
            $id = mt_rand(0, 10000);
            // Write log file.
            foreach (self::$logBuffer as $logItem) {
                fwrite($appFile, sprintf("%s (%05d) [%s] %7s - %s\n", $logItem['date'], $id, APP, $logItem['level'], $logItem['msg']));
            }
            // Close file.
            fclose($appFile);
        }
    }

    /**
     * Retrieve the log data and statistics. When this method is called, it is
     * assumed that moment as the end time.
     *
     * @return array  The log data and statistics.
     */
    public static function getLog () {
        // Get the request time.
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        // Return the log info.
        return array(
            'time' => self::getReadableTime($time),
            'memory' => self::getReadableSize(memory_get_peak_usage()),
            'files' => get_included_files(),
            'repo' => self::$numRepoAccess,
            'logs' => self::$logBuffer
        );
    }

    /**
     * Log the memory usage of an object. If no object, log the memory usage of
     * the current script at this very moment. Logged only when MEMO level is set.
     *
     * @param string  $msg     A message.
     * @param object  $object  A variable to measure (optional).
     */
    public static function memory ($msg, $object = null) {
        // Check the log configuration.
        if (self::$level & 16) {
            // Calculate the memory usage.
            $memory = ($object !== null) ? strlen(serialize($object)) : memory_get_usage();
            $msg.= ' ('.self::getReadableSize($memory).')';
            // Log the new memory entry.
            self::log('memory', $msg);
        }
    }

    /**
     * Log a repository access. If a start time is provided, calculate the gap
     * between now and the start time. Logged only when REPO level is set.
     *
     * @param string  $msg        A repository query.
     * @param float   $startTime  The start UNIX time-stamp of the query (optional).
     */
    public static function repo ($msg, $startTime = null) {
        // Check the log configuration.
        if (self::$level & 64) {
            // Get the gap between the start time and the end time.
            if ($startTime) {
                $duration = microtime(true) - $startTime;
                $msg.= ' ('.self::getReadableTime($duration).')';
            }
            // Log the new repository access and updates the counter.
            self::log('repo', $msg);
            self::$numRepoAccess += 1;
        }
    }

    /**
     * Set a new log level. The log values should be a comma separated list of
     * one of each of this values: NONE, ERROR, WARNING and DEBUG. Optionally it
     * is possible to add one or more of this values: SYS, MEMO, TIME and REPO.
     *
     * @param string  $levels  A comma separated list of levels.
     */
    public static function setLevel ($levels) {
        self::$level = 0;
        foreach (explode(',', $levels) as $level) {
            self::$level += constant('self::'.trim($level));
        }
    }

    /**
     * Log a message to the system log. The extra parameters will be
     * interpolated with the message. Logged only when SYS level is set.
     *
     * @param string  $msg  A message.
     */
    public static function sys ($msg) {
        // Check the log configuration.
        if (self::$level & 8) {
            // Get the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Log the new error entry.
            self::log('system', $msg);
        }
    }

    /**
     * Log a message with the amount of seconds passed since the request arrived
     * at the server until this very moment. The extra parameters will be
     * interpolated with the message. Logged only when TIME level is set.
     *
     * @param string  $msg  A message.
     */
    public static function time ($msg) {
        // Check the log configuration.
        if (self::$level & 32) {
            // Get the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Calculate the time gap.
            $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            $msg.= ' ('.self::getReadableTime($time).')';
            // Log the new time entry.
            self::log('time', $msg);
        }
    }

    /**
     * Log a warning message. The extra parameters will be interpolated with the
     * message. Logged only when ERROR or WARNING level are set.
     *
     * @param string  $msg  A warning message.
     */
    public static function warning ($msg) {
        // Check the log configuration.
        if (self::$level & 2) {
            // Get the message interpolated with the context.
            $msg = self::interpolate(func_get_args());
            // Log the new warning entry.
            self::log('warning', $msg);
        }
    }


    /**
     * Change the unit of a file/memory size in bytes to a unit that fits better
     * with that size number. The minimum size unit is byte and the maximum size
     * unit is terabyte.
     *
     * @param  int     $size  A file/memory size in bytes.
     * @return string         A file/memory size that fits better that size number.
     */
    private static function getReadableSize ($size) {
        // Default number formats and available units measures.
        $numFormat = '%01.2f %s';
        $formats = array('bytes','kB','MB','GB','TB');
        // Calculate best unit for the number size.
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
        // Return the formatted string.
        return sprintf($numFormat, $size, $unit);
    }

    /**
     * Change the unit of an amount of time in seconds to a unit that fits better
     * with that amount of time. The minimum time unit is milliseconds and the
     * maximum time unit is minutes.
     *
     * @param  float   $time  An amount of time in seconds.
     * @return string         A time that fits better that amount of time.
     */
    private static function getReadableTime ($time) {
        if ($time < 1) {
            // If the time duration is less than a second, use milliseconds.
            $format = '%.3f %s';
            $unit = 'msec';
            $time = $time * 1000;
        } else if ($time >= 1 && $time < 60) {
            // If the time duration is between 1 and 60 seconds, use seconds.
            $unit = 'sec';
            $format = '%.3f %s';
        } else {
            // If the time duration is higher than 60 seconds, use minutes.
            $format = '%.2f %s';
            $unit = 'min';
            $time = floor($time / 60) + ($time % 60) / 100;
        }
        // Return the formatted string.
        return sprintf($format, $time, $unit);
    }

    /**
     * Interpolate the first string of the array with the other ones.
     *
     * @param  array   $context  Strings to interpolate with.
     * @return string            The first string interpolated with the others.
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
     * Log a message.
     *
     * @param string  $level  A log level.
     * @param string  $msg    A message.
     */
    private static function log ($level, $msg) {
        // Log the new entry.
        self::$logBuffer[] = array(
            'level' => $level,
            'date' => date('Y-m-d H:i:s'),
            'msg' => $msg
        );
    }
}