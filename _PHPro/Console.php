<?php
/**
 * This class provides all the log methods needed to retrieve information about 
 * the framework's actions (DAL access, errors, memory usage, system info, time 
 * consumed or warnings). This log methods must be placed wisely in your code 
 * in order to get a good debug information with them. All the framework's 
 * scripts are using the system log method in key points to easy debug.
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
class Console {

    /**
     * Stores all the log data.
     */
    private static $logBuffer = array();

    /**
     * Stores the total statistics (memory used, total time, files included and 
     * number of DAL access).
     */
    private static $totalStats = array(
        'time' => 'Unknown',
        'memory' => 'Unknown',
        'files' => array(),
        'dal' => 0
    );

    /**
     * Because of static nature of the class, the constructor is set as a 
     * private method to avoid incorrect use.
     */
    private function __construct () {}

    /**
     * Changes the unit of a file/memory size in bytes to a unit that fits 
     * better with that size number. The maximum size unit is TeraByte.
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
     * better with that amount of time.
     * 
     * @param time  a float as an amount of time in seconds
     */
    private static function getReadableTime ($time) {
        if ($time < 1) {
            // If the time duration is less than a second, uses milliseconds.
            $format = '%01.3f %s';
            $unit = 'msec';
            $time = $time * 1000;
        } else if ($time >= 1 && $time < 60) {
            // If the time duration is between 1 and 60 seconds, uses seconds.
            $unit = 'sec';
            $format = '%01.3f %s';
        } else {
            // If the time duration is higher than 60 seconds, uses minutes.
            $format = '%01.2f %s';
            $unit = 'min';
            $time = $time / 60;
        }
        // Returns the formatted string.
        return sprintf($format, $time, $unit);
    }

    /**
     * Calculates the request statistics and processes the logs.
     */
    private static function processLog () {
        // Calculates the total request time.
        list($msec, $sec) = explode(' ', microtime());
        $time = ((float)$sec + (float)$msec) - $_SERVER['REQUEST_TIME_FLOAT'];
        // Calculates the statistics.
        self::$totalStats['time'] = self::getReadableTime($time);
        self::$totalStats['memory'] = self::getReadableSize(memory_get_peak_usage());
        self::$totalStats['files'] = get_included_files();
    }


    /**
     * Writes the log file whenever the appropriated flag is set on the 
     * application global configuration variable.
     * 
     * @throws  SYSException(0100) if the log path does not exist
     */
    public static function flush () {
        global $cfg;
        // Checks if the application global configuration variable is set to write log files.
        if (!$cfg['LOG']['debug'] || ($cfg['LOG']['debug'] === 'OnlyDisplay')) {
            return false;
        }
        // Checks if the log directory exists.
        if (!is_dir(APP.$cfg['PATHS']['logs'])) {
            throw new SYSException('0100', array(
                'path' => $cfg['PATHS']['logs']
            ));
        }
        // Opens the file.
        $filePath = APP.$cfg['PATHS']['logs'].'/log'.date('Y.m.d').'.txt';
        $file = fopen($filePath, 'a');
        // Formats the log messages and writes them.
        foreach (self::$logBuffer as $logItem) {
            switch ($logItem['type']) {
                case 'dal':
                    $msg = '('.$logItem['date'].') DAL >> '.$logItem['query']."\n";
                    $msg.= '('.$logItem['date'].') DAL >> '.$logItem['duration']."\n";
                    break;
                case 'error':
                    $msg = '('.$logItem['date'].') Error on line '.$logItem['line'].' >> '.$logItem['code'].' '.$logItem['msg']."\n".
                            '('.$logItem['date'].') Error file: '.$logItem['file']."\n".
                            '('.$logItem['date'].') Error details: '.$logItem['details']."\n";
                    break;
                case 'log':
                    $msg = '('.$logItem['date'].') Log >> '.$logItem['msg']."\n";
                    break;
                case 'memory':
                    $msg = '('.$logItem['date'].') Memory >> '.$logItem['name'].' - '.$logItem['memory']."\n";
                    break;
                case 'sys':
                    $msg = '('.$logItem['date'].') System >> '.$logItem['msg']."\n";
                    break;
                case 'time':
                    $msg = '('.$logItem['date'].') Time >> '.$logItem['msg'].' - '.$logItem['time']."\n";
                    break;
                case 'warning':
                    $msg = '('.$logItem['date'].') Warning >>  '.$logItem['msg']."\n";
                    break;
                default:
                    $msg = 'Unknown type of log.'."\n";
            }
            fwrite($file, $msg);
        }
        // Closes the file.
        fclose($file);
    }

    /**
     * Retrieves the log data and statistics.
     */
    public static function getLog () {
        // Calculates total statistics.
        self::processLog();
        // Returns the log info.
        return array(
            'stats' => self::$totalStats,
            'log' => self::$logBuffer
        );
    }

    /**
     * Logs a message to console. The log includes the creation time.
     * 
     * @param msg  a string with the message
     */ 
    public static function log ($msg) {
        // Logs the new entry.
        self::$logBuffer[] = array(
            'type' => 'log',
            'date' => date('Y-m-d H:i:s'),
            'msg' => print_r($msg, true)
        );
    }

    /**
     * Logs a DAL access to console. The log includes the creation time. If a 
     * start time and an end time is provided, calculates the gap between them.
     * 
     * @param query      a string with a DAL query
     * @param startTime  a string with a query start time formatted as 'msec sec' (optional)
     * @param endTime    a string with a query end time formatted as 'msec sec' (optional)
     */
    public static function logDAL ($query, $startTime = null, $endTime = null) {
        // Gets the gap between the start time and the end time if available.
        if ($startTime && $endTime) {
            list($msecStart, $secStart) = explode(' ', $startTime);
            list($msecEnd, $secEnd) = explode(' ', $endTime);
            $duration = ((float)$secEnd + (float)$msecEnd) - ((float)$secStart + (float)$msecStart);
        }
        // Logs the new DAL entry.
        self::$logBuffer[] = array(
            'type' => 'dal',
            'date' => date('Y-m-d H:i:s'),
            'query' => print_r($query, true),
            'duration' => self::getReadableTime($duration)
        );
        // Updates the query count.
        self::$totalStats['dal'] += 1;
    }

    /**
     * Logs the message, file and line of an exception error and an optional 
     * message to console. The log includes the creation time.
     * 
     * @param request  an array with a request error information
     * @param msg      a string with a message (optional)
     */
    public static function logError ($request, $msg = null) {
        // Logs the new error entry.
        self::$logBuffer[] = array(
            'type' => 'error',
            'date' => date('Y-m-d H:i:s'),
            'code' => $request['code'],
            'file' => $request['file'],
            'line' => $request['line'],
            'msg' => $request['msg'],
            'details' => $request['details']
        );
    }

    /**
     * Logs the memory usage of the object. If no object, logs the memory usage 
     * of the current script at this very moment. The log includes the creation 
     * time.
     * 
     * @param object   a variable to measure (optional)
     * @param objName  a string with an object name (optional)
     */
    public static function logMemory ($object = false, $objName = null) {
        // Calculates the memory usage.
        $memory = $object ? strlen(serialize($object)) : memory_get_usage();
        // Logs the new memory entry.
        self::$logBuffer[] = array(
            'type' => 'memory',
            'date' => date('Y-m-d H:i:s'),
            'memory' => self::getReadableSize($memory),
            'name' => $objName
        );
    }

    /**
     * Logs a system message to console. The log includes the creation time. 
     * The purpose of this method is to separate the system logs and the user 
     * logs, so they can be filtered conveniently. So, this method must be 
     * used only by framework's scripts.
     * 
     * @param msg  a string with a message
     */ 
    public static function logSys ($msg = null) {
        // Logs the new system entry.
        self::$logBuffer[] = array(
            'type' => 'sys',
            'date' => date('Y-m-d H:i:s'),
            'msg' => print_r($msg, true)
        );
    }

    /**
     * Logs the amount of seconds passed since the request arrived until this 
     * very moment and an optional message to console.
     * 
     * @param msg  a string with a message
     */
    public static function logTime ($msg = null) {
        // Calculates the time gap.
        list($msec, $sec) = explode(' ', microtime());
        $time = ((float)$sec + (float)$msec) - $_SERVER['REQUEST_TIME_FLOAT'];
        // Logs the new time time entry.
        self::$logBuffer[] = array(
            'type' => 'time',
            'date' => date('Y-m-d H:i:s'),
            'time' => self::getReadableTime($time),
            'msg' => print_r($msg, true)
        );
    }

    /**
     * Logs a warning message to console. The log includes the creation time.
     * 
     * @param msg  a string with a message
     */ 
    public static function logWarning ($msg = null) {
        // Logs the new warning entry.
        self::$logBuffer[] = array(
            'type' => 'warning',
            'date' => date('Y-m-d H:i:s'),
            'msg' => print_r($msg, true)
        );
    }
}
?>