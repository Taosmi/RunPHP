<?php
    require_once(SYSTEM.'/iDAL.php');

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
class dalMySQL implements iDAL {

    /**
     * The DAL (Data Access Layer) connection configuration.
     */
    private $server, $user, $pass, $db;

    /**
     * The DB link resource and the query fields to retrieve.
     */
    private  $link, $fields;

    /**
     *  The DB table and the DB primary key field.
     */
    private $table, $pk;

    /**
     * Transforms an options array into a query string that can be delivered 
     * to the DB. If no options, returns an empty string.
     * 
     * @param options  an associative array with the options (optional)
     * @return         a string with the options condition
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
        if (isset($options['paginate'])) {
            $sql.= ' LIMIT '.$options['paginate']['from'].' '.$options['paginate']['to'];
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
     * @param join  the character in between pair of values (optional)
     * @return      a query string
     */
    private function toQuery ($array, $join = ',') {
        $query = '';
        foreach($array as $key => $value) {
            $query .= $key.'=\''.$value.'\''.$join;
        }
        return rtrim($query, $join);
    }


    /**
     * Loads the connection string. The string must contain 4 values separated 
     * by comma ','. The values must be ordered this way: server, user name, 
     * password and DB name.
     * 
     * @param cfg  a string with the info required to establish a connection
     */
    public function __construct ($cfg) {
        list($this->server, $this->user, $this->pass, $this->db) = explode(',', $cfg);
    }

    /**
     * Adds a new item into the persistence system.
     * 
     * @param data  an associative array with the new item data
     * @return      a string with the ID for the new item
     */
    public function add ($data) {
        // Gets the item keys and values.
        $keys = implode(',', array_keys($data));
        $values = '"'.implode('","', array_values($data)).'"';
        // Insert query.
        $sql = 'INSERT INTO '.$this->table.' ('.$keys.') VALUES ('.$values.')';
        $qResult = $this->query($sql);
        // Returns the item ID.
        return mysql_insert_id($this->link);
    }

    /**
     * Retrieves all the items that matches the options provided. If no options,
     * retrieves all of them.
     * 
     * @param options  an associative array with the filter criteria (optional)
     * @return         an array with the found items
     */
    public function find ($options = null) {
        // Gets the fields to retrieve.
        $fields = $this->fields ? $this->fields : '*';
        // Select query.
        $sql = 'SELECT '.$fields.' FROM '.$this->table.$this->parseOptions($options);
        // Query time.
        return $this->query($sql, true);
    }

    /**
     * Sets the target table and the primary key field of the DB. The entity 
     * and the primary key must be separated by comma.
     * 
     * @param resource  a string with the table name and the primary key field
     */
    public function from ($resource) {
        list($this->table, $this->pk) = explode(',', $resource);
    }

    /**
     * Retrieves the item from the DB that matches the primary key provided. If 
     * no item matches the primary key, returns an empty array.
     * 
     * @param pk  a string with an item primary key
     * @return    an associative array with the item data or empty
     */
    public function get ($pk) {
        // Gets the fields to retrieve.
        $fields = $this->fields ? $this->fields : '*';
        // Select query.
        $sql = 'SELECT '.$fields.' FROM '.$this->table.' WHERE '.$this->pk.' = '.$pk;
        $result = $this->query($sql, true);
        // Returns the result.
        return $result;
    }

    /**
     * Modifies the items that matches the options provided with the new data. 
     * If no options, modifies all of them.
     * 
     * @param data     an associative array with the new data
     * @param options  an associative array with the filter criteria (optional)
     * @return         the number of modified items
     */
    public function modify ($data, $options = null) {
        // Update query.
        $sql = 'UPDATE '.$this->table.' SET '.$this->toQuery($data).$this->parseOptions($options);
        $this->query($sql);
        // Returns the number of items updated.
        return mysql_affected_rows($this->link);
    }

    /**
     * Executes a custom query directly into the persistence system.
     * 
     * @param query  a string with the query
     * @param fetch  a boolean, if true fetches the result into an associative array (optional)
     * @return       a query result or an associative array if fetched
     * @throws       SYSException() if the query fails
     */
    public function query ($query, $fetch = false) {
        // Process the query.
        $qStart = microtime();
        $qResult = @mysql_query($query, $this->link);
        $qEnd = microtime();
        Console::logDAL($query, $qStart, $qEnd);
        // Checks for errors.
        if ($qResult === false) {
            throw new SYSException('', array(
                'error' => mysql_error($this->link),
                'query' => $query
            ));
        }
        // Fetches the result.
        if ($fetch) {
            $itemList = array();
            while ($item = mysql_fetch_assoc($qResult)) {
                $itemList[] = $item;
            }
            mysql_free_result($qResult);
            return $itemList;
        }
        return $qResult;
    }

    /**
     * Removes the items that matches the options provided. If no options, 
     * removes all of them.
     * 
     * @param options  an associative array with the filter criteria (optional)
     * @return         the number of removed items
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
     * @param data  a string with the fields name separated by comma
     */
    public function select ($data) {
        $this->fields = $data;
    }

    /**
     * Creates a connection to the persistence system. If there is already an 
     * available connection, it will be reused.
     * 
     * @throws  SYSException() if the connection could not be established
     */
    public  function connect () {
        if (!is_resource($this->link)) {
            // Connects to the DB.
            $start = microtime();
            $this->link = mysql_connect($this->server, $this->user, $this->pass);
            $end = microtime();
            Console::logDAL('Connecting to the DDBB ('.$this->server.')('.$this->db.')', $start, $end);
            if (!$this->link) {
                throw new SYSException('', array(
                    'error' => mysql_error(),
                    'server' => $this->server,
                    'user' => $this->user
                ));
            }
            // Selects the DB.
            $result = mysql_select_db($this->db, $this->link);
            if (!$result) {
                throw new SYSException('', array(
                    'error' => mysql_error(),
                    'server' => $this->server,
                    'user' => $this->user
                ));
            }
            mysql_query('SET NAMES utf8;', $this->link);
        }
    }

    /**
     * Closes the connection to the persistence system.
     * 
     * @throws  SYSException() if the connection could not be closed
     */
    public function disconnect () {
        $start = microtime();
        $result = mysql_close($this->link);
        $end = microtime();
        Console::logDAL('Disconnecting from the DDBB ('.$this->server.')('.$this->db.')', $start, $end);
        if (!$result) {
            throw new SYSException('', array(
                'error' => mysql_error($this->link),
                'server' => $this->server
            ));
        }
    }

    /**
     * Starts a new block of operations to the persistence system. This method 
     * is not mandatory when querying the persistence system.
     */
    public function init () {
        $this->query('START TRANSACTION');
    }

    /**
     * Consolidates the changes of the current block of operations to the 
     * persistence system. This method is not mandatory when the init() method 
     * was not used before.
     */
    public function commit () {
        $this->query('COMMIT');
    }

    /**
     * Discards the changes of the current block of operations to the 
     * persistence system. This method is not mandatory when querying the 
     * persistence system.
     */
    public function rollback () {
        $this->query('ROLLBACK');
    }

    /**
     * Makes a backup of the entities provided. If no entities, makes a backup 
     * of the whole persistence system.
     * 
     * @param entities  a string with the entities separated by comma (optional)
     * @return          a backup of the entities provided
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
}
?>