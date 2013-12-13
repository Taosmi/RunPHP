<?php

namespace ProWeb\Repositories;
use ProWeb;


/** 
 * This class implements the DAL (Data Access Layer) Class and wraps the 
 * access to a MySQL DB into a persistence system totally decoupled.
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
class RepositoryMySQL implements IRepository {

    /**
     * The DAL (Data Access Layer) connection configuration.
     */
    private $server, $user, $pass, $db;

    /**
     * The DB link, DB table and and the query fields to retrieve.
     */
    private $link, $table, $fields, $objectName;


    /**
     * Loads the connection string. The string must contain 4 values separated 
     * by comma ','. The values must be ordered this way: server, user name, 
     * password and DB name.
     * 
     * @param cfg  A string with the info required to establish a connection.
     * @throws     ErrorException() if no connection string is supplied.
     */
    public function __construct ($cfg) {
        if (!$cfg) {
            throw new ProWeb\ErrorException('0010', array(
                'connection' => $cfg
            ));
        }
        // Gets the DB configuration.
        list($this->server, $this->user, $this->pass, $this->db) = explode(',', $cfg);
    }

    /**
     * Adds a new object to the repository.
     * 
     * @param object  A new object.
     * @return        Returns true if the object was successfully added.
     */
    public function add ($object) {
        // Gets the object keys and values (only the not falsy).
        $objData = array_filter(get_object_vars($object));
        $keys = implode(',', array_keys($objData));
        $values = '"'.implode('","', array_values($objData)).'"';
        // Query time.
        $sql = 'INSERT INTO '.$this->table.' ('.$keys.') VALUES ('.$values.')';
        return $this->query($sql);
    }

    /**
     * Retrieves all the objects that matches the options provided. If no options,
     * retrieves all of them.
     * 
     * @param options  An associative array with the filter criteria (optional).
     * @return         An array with the found objects or an empty array.
     */
    public function find ($options = null) {
        // Gets the fields to retrieve.
        $fields = $this->fields ? $this->fields : '*';
        // Query time.
        $sql = 'SELECT '.$fields.' FROM '.$this->table.$this->parseOptions($options);
        $qResult = $this->query($sql);
        // Fetches the result.
        $itemList = array();
        while ($item = mysql_fetch_assoc($qResult)) {
            $object = new $this->objectName();
            foreach ($item as $key => $value) {
                $object->$key = $value;
            }
            $itemList[] = $object;
        }
        mysql_free_result($qResult);
        return $itemList;
    }

    /**
     * Sets the target repository.
     * 
     * @param resource  A string with the repository name.
     */
    public function from ($table) {
        $this->table = $table;
    }

    /**
     * Modifies the items that matches the options provided with the new data. 
     * If no options, modifies all of them.
     * 
     * @param data     An associative array with the new data.
     * @param options  An associative array with the filter criteria (optional).
     * @return         The number of modified items.
     */
    public function modify ($object, $options = null) {
        // Update query.
        $objData = get_object_vars($object);
        $sql = 'UPDATE '.$this->table.' SET '.$this->toQuery($objData).$this->parseOptions($options);
        $this->query($sql);
        // Returns the number of items updated.
        return mysql_affected_rows($this->link);
    }

    /**
     * Executes a custom query directly into the persistence system.
     * 
     * @param query  A string with the query.
     * @return       A query result.
     * @throws       ErrorException() if the query fails.
     */
    public function query ($query) {
        // Connects to the DB if needed.
        $this->connect();
        // Process the query.
        $qStart = microtime(true);
        $qResult = @mysql_query($query, $this->link);
        ProWeb\Logger::repo($query, $qStart);
        // Checks for errors.
        if ($qResult === false) {
            throw new ProWeb\ErrorException('0011', array(
                'error' => mysql_error($this->link).'\n'.$query
            ));
        }
        return $qResult;
    }

    /**
     * Removes the items that matches the options provided. If no options, 
     * removes all of them.
     * 
     * @param options  An associative array with the filter criteria (optional).
     * @return         The number of removed items.
     */
    public function remove ($options = null) {
        // Delete query.
        $sql = 'DELETE FROM '.$this->table.$this->parseOptions($options);
        $this->query($sql);
        // Returns the number of items deleted.
        return mysql_affected_rows($this->link);
    }

    /**
     * Sets the fields that will be retrieved by a find method. If no fields 
     * are set, the find method will retrieve all of them.
     * 
     * @param data  A string with the fields name separated by comma.
     */
    public function select ($data) {
        $this->fields = $data;
    }

    /**
     * Sets the class name for the objects retrieved from the repository.
     * 
     * @param objectName  The full class name of the object.
     */
    public function to ($objectName) {
        $this->objectName = $objectName;
    }


    /**
     * Starts a new block of operations. This method is not mandatory when 
     * querying the repository.
     */
    public function init () {
        $this->query('START TRANSACTION');
    }

    /**
     * Consolidates the changes of the current block of operations. This method 
     * is mandatory when the init() method was used before.
     */
    public function commit () {
        $this->query('COMMIT');
    }

    /**
     * Discards the changes of the current block of operations. This method is 
     * not mandatory when querying the repository.
     */
    public function rollback () {
        $this->query('ROLLBACK');
    }

    /**
     * Makes a backup of the repositories provided. If no repositories backups 
     * of all them.
     * 
     * @param repositories  A string with the repositories separated by comma (optional)
     * @return              A backup of the repositories provided.
     */
    public function backup ($entities = null) {
        // Gets the tables.
        if ($entities) {
            $tables = explode(',', $entities);
        } else {
            $qResult = mysql_query('SHOW TABLES');
            while ($item = mysql_fetch_row($qResult)) {
                $tables[] = $item[0];
            }
        }
        // Gets the tables data.
        $script = '';
        foreach ($tables as $table) {
            // Drops the table if already exists.
            $script.= "\n".'DROP TABLE IF EXISTS '.$table.';'."\n";
            // Creates the table.
            $qResult = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table.';'));
            $script.= $qResult[1]."\n";
            // Populates the table with the data.
            $qResult = mysql_query('SELECT * FROM '.$table.';');
            while ($item = mysql_fetch_row($qResult)) {
                $script.= 'INSERT INTO '.$table.' VALUES(';
                // Cleans the parameters.
                foreach ($item as &$value) {
                    $value = addslashes(str_replace("\r\n", "\\r\\n", $value));
                }
                $script.= '"'.implode('","', $item).'"';
                $script.= ');'."\n";
            }
        }
        // Writes the file.
        $file = fopen(APP.'/dal-backup-'.date('Ymd').'.sql', 'w+');
        fwrite($file, $script);
        fclose($file);
    }


    /**
     * Creates a connection to the repository. If there is already an available 
     * connection, it will be reused.
     * 
     * @throws  ErrorException() if the connection could not be established.
     */
    private function connect () {
        if (!is_resource($this->link)) {
            // Connects to the DB.
            $start = microtime(true);
            $this->link = mysql_connect($this->server, $this->user, $this->pass);
            ProWeb\Logger::repo('Connecting to the DDBB ('.$this->server.')('.$this->db.')', $start);
            if (!$this->link) {
                throw new EXTException('0012', array(
                    'error' => mysql_error(),
                    'server' => $this->server,
                    'user' => $this->user
                ));
            }
            // Selects the DB.
            $result = mysql_select_db($this->db, $this->link);
            if (!$result) {
                throw new EXTException('', array(
                    'error' => mysql_error(),
                    'server' => $this->server,
                    'user' => $this->user
                ));
            }
            // Sets session transaction isolation level read committed.
            mysql_query('SET NAMES utf8;', $this->link);
        }
    }

    /**
     * Closes the connection to the repository.
     * 
     * @throws  ErrorException() if the connection could not be closed.
     */
    private function disconnect () {
        $start = microtime(true);
        $result = mysql_close($this->link);
        ProWeb\Logger::repo('Disconnecting from the DDBB ('.$this->server.')('.$this->db.')', $start);
        if (!$result) {
            throw new EXTException('0013', array(
                'error' => mysql_error($this->link),
                'server' => $this->server
            ));
        }
    }

    /**
     * Transforms the options array into a query string that can be delivered 
     * to the DB. If no options, returns an empty string.
     * 
     * @param options  An associative array with the options (optional).
     * @return         A string with the options condition.
     */
    private function parseOptions ($options = null) {
        $sql = '';
        if (!$options) {
            return $sql;
        }
        if (isset($options['condition'])) {
            $sql.= ' WHERE '.$options['condition'];
        }
        if (isset($options['groupBy'])) {
            $sql.= ' GROUP BY '.$options['groupBy'];
        }
        if (isset($options['orderBy'])) {
            $sql.= ' ORDER BY '.$options['orderBy'];
        }
        if (isset($options['limit'])) {
            $limit = $options['limit'];
            $offset = isset($options['offset']) ? $options['offset'] : '0';
            $sql.= ' LIMIT '.$offset.','.$limit;
        }
        return $sql;
    }


    /**
     * Transforms an associative array into a string. By default, the pair of 
     * values will be joined by comma.
     * Example: 
     * Source: array('key' => 'value, 'key' => 'value');
     * Result: "key='value',key='value'"
     * 
     * @param join  The character in between pair of values (optional).
     * @return      A query string.
     */
    private function toQuery ($array, $join = ',') {
        $query = '';
        foreach($array as $key => $value) {
            $query .= $key.'=\''.$value.'\''.$join;
        }
        return rtrim($query, $join);
    }
}
?>