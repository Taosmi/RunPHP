<?php

namespace proWeb\plugins;
use proWeb\IRepository, proWeb\ErrorException, proWeb\Logger;
use PDO, PDOException;

/** 
 * This class implements the repository interface with PDO technology.
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
class RepositoryPDO implements IRepository {

    /**
     * A DB resource, a DB table and the query fields to retrieve.
     */
    private $pdo, $table, $fields, $objectName;


    /**
     * The connection string must be formatted as:
     *      tech:host=hostname;dbname=dbname,user,password
     * The available technologies are the same that the PHP PDO drivers.
     * This is an example of MySQL connection string:
     *      mysql:host=db18.1and1.es;dbname=db355827412,guest,12345
     *
     * @param string $connection  A connection string.
     * @throws                    ErrorException if the connection fails.
     */
    public function __construct ($connection) {
        // Gets the DB resource.
        try {
            list($resource, $user, $pwd) = explode(',', $connection);
            $start = microtime(true);
            $this->pdo = new PDO($resource, $user, $pwd);
            $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            Logger::repo('Connecting to the DDBB ('.$resource.')', $start);
            $this->query('SET NAMES utf8');
        } catch (PDOException $e) {
            throw new ErrorException(0110, __('The connection to the persistence has failed.', 'system'), array(
                'error' => $e->getMessage(),
                'resource' => $resource
            ), 'system');
        }
    }


    public function add ($item) {
        // Gets the object keys.
        $objData = get_object_vars($item);
        $keys = array_keys($objData);
        // Query time.
        $sql = 'INSERT INTO '.$this->table.' ('.implode(',', $keys).') VALUES (:'.implode(',:', $keys).')';
        $this->query($sql, $objData);
        return $this->pdo->lastInsertId();
    }

    public function find ($options = null) {
        // Gets the fields to retrieve.
        $fields = $this->fields ? $this->fields : '*';
        // Query time.
        $sql = 'SELECT '.$fields.' FROM '.$this->table.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Fetches the result.
        if ($this->objectName) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->objectName);
        } else {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        }
        return $statement->fetchAll();
    }

    public function from ($resource) {
        $this->table = $resource;
    }

    public function modify ($item, $options = null) {
        // Update query.
        $sql = 'UPDATE '.$this->table.' SET '.$this->toQuery($item).' '.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Returns the number of items updated.
        return $statement->rowCount();
    }

    public function query ($query, $data = null) {
        try {
            // Process the query.
            if ($data) {
                $qStart = microtime(true);
                $statement = $this->pdo->prepare($query);
                $statement->execute($data);
                Logger::repo($query, $qStart);
            } else {
                $qStart = microtime(true);
                $statement = $this->pdo->query($query);
                Logger::repo($query, $qStart);
            }
            return $statement;
        } catch (PDOException $e) {
            throw new ErrorException(0111, __('The query to the persistence has failed.', 'system'), array(
                'error' => $e->getMessage(),
                'query' => $query
            ), 'system');
        }
    }

    public function remove ($options = null) {
        // Delete query.
        $sql = 'DELETE FROM '.$this->table.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Returns the number of items deleted.
        return $statement->rowCount();
    }

    public function select ($fields) {
        $this->fields = $fields;
    }

    public function to ($objectName) {
        $this->objectName = $objectName;
    }

    public function beginTransaction () {
        $this->pdo->beginTransaction();
    }

    public function commit () {
        $this->pdo->commit();
    }

    public function rollback () {
        $this->pdo->rollBack();
    }

    public function backup ($fileName = null) {
        // Checks if the the table is set.
        if (!$this->table) {
            echo "ERROR";
        }
        // Checks the file name.
        if (!$fileName) {
            $fileName = 'repo_'.$this->table;
        }
        $script = '-- Table creation'."\r\n";
        // Gets the table creation script.
        $script.= "\n".'DROP TABLE IF EXISTS '.$this->table.';'."\n";
        $stmtTable = $this->pdo->query('SHOW CREATE TABLE '.$this->table.';');
        $stmtTable ->setFetchMode(PDO::FETCH_ASSOC);
        $script.= $stmtTable->fetchColumn(1).";\n";
        // Gets the data script.
        $script.= '-- Data'."\r\n";
        $stmtData = $this->pdo->query('SELECT * FROM '.$this->table.';');
        $stmtData->setFetchMode(PDO::FETCH_ASSOC);
        while ($item = $stmtData->fetch()) {
            $script.= 'INSERT INTO '.$this->table.' VALUES(';
            // Cleans the parameters.
            foreach ($item as &$value) {
                $value = addslashes(str_replace("\r\n", "\\r\\n", $value));
            }
            $script.= '"'.implode('","', $item).'"';
            $script.= ');'."\n";
        }
        // Writes the file.
        $file = fopen(RESOURCES.'/'.$fileName.'.'.date('Ymd.His').'.sql', 'w+');
        fwrite($file, $script);
        fclose($file);
    }


    /**
     * Transforms the options array into a string that can be delivered to the DB.
     * If no options, returns an empty string.
     *
     * @param array $options  The options (optional).
     * @return string         The options condition.
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
     * Transforms an associative array into a separated by comma key - value
     * pairs string. By default, the pair of values will be joined by comma.
     * Example: 
     * Source: array(key => value, key => value);
     * Result: key='value',key='value'
     *
     * @param object  $item  A item with public data.
     * @param string  $join  The character in between pair of values (optional).
     * @return string       A separated by comma key - value pairs string.
     */
    private function toQuery ($item, $join = ',') {
        $query = '';
        foreach(get_object_vars($item) as $key => $value) {
            $query .= $key.'=\''.$value.'\''.$join;
        }
        return rtrim($query, $join);
    }
}