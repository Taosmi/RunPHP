<?php

namespace runPHP\plugins;

/**
 * This class implements functionality to send emails easier.
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
class Email {

    /**
     * Send an email. This is the most basic method where the content must be
     * provided as a string. The HTML flag is false by default.
     *
     * @param  string   $from     The email source.
     * @param  string   $to       The destination email.
     * @param  string   $subject  The email subject.
     * @param  string   $content  The email content.
     * @param  boolean  $html     If the email content is on HTML format.
     * @return boolean            True if the email was sent without error, otherwise false.
     */
    public static function send ($from, $to, $subject, $content, $html = false) {
        $headers = '';
        // Set HTML headers.
        if ($html) {
            $headers = "MIME-Version: 1.0\r\n";
            $headers.= "Content-type: text/html; charset=UTF-8\r\n";
        }
        // Set the from email direction.
        $headers.= "From: $from\r\n";
        // Send the email.
        return mail($to, $subject, $content, $headers);
    }

    /**
     * Send an email. The content is on a HTML file. The from and to email
     * directions must be provided as keys of the dynData parameter. The subject
     * of the email is retrieved of the HTML title tag.
     *
     * @param  string  $file     The file with the HTML content.
     * @param  array   $dynData  The keys that will be replaced by their values.
     * @return boolean           True if the email was sent without error, otherwise false.
     */
    public static function fromFile ($file, $dynData = array()) {
        // Check if the file exists.
        if (!file_exists($file)) {
            return false;
        }
        // Load the email content.
        $content = file_get_contents($file);
        // Get the subject.
        $start = strpos($content, '<title>') + 7;
        $end = strpos($content, '</title>') - $start;
        $subject = substr($content, $start, $end);
        // Parse the dynamic data of the content.
        $content = preg_replace_callback('/\\$(\w+)/', function ($match) use ($dynData) {
            return $dynData[$match[1]];
        }, $content);
        // Send the email.
        return self::send($dynData['from'], $dynData['to'], $subject, $content, true);
    }
}