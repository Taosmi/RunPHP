<?php

namespace runPHP\plugins;
use runPHP\IRepository, runPHP\ErrorException, runPHP\Logger;
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
     * The PDO object.
     * @var string
     */
    private $pdo;

    /**
     * The DB table.
     * @var string
     */
    private $table;

    /**
     * The table primary keys.
     * @var array
     */
    private $keys;

    /**
     * The fields to retrieve when querying.
     * @var string
     */
    private $fields;

    /**
     * The full class name to cast from the DB results.
     * @var string
     */
    private $objectName;


    public function __construct ($connection, $objectName, $pks = null) {
        try {
            // Get the DB parameters.
            list($resource, $user, $pwd) = explode(',', $connection);
            // Get the PDO resource.
            $start = microtime(true);
            $this->pdo = new PDO($resource, $user, $pwd);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            Logger::repo('Connecting to the DDBB ('.$resource.')', $start);
            $this->query('SET NAMES utf8');
            // Set the repository configuration.
            $this->objectName = $objectName;
            $this->table = substr($objectName, strrpos($objectName, '\\') + 1);
            // Set the primary keys.
            if ($pks) {
                // Get primary keys from argument.
                $this->keys = explode(',', $pks);
            } else {
                // Get primary keys querying the DB.
                $pksResult = $this->query('SHOW KEYS FROM '.$this->table);
                $pksResult->setFetchMode(PDO::FETCH_COLUMN, 4);
                $this->keys = $pksResult->fetchAll();
            }
        } catch (PDOException $e) {
            throw new ErrorException(__('The connection to the persistence has failed.', 'system'), array(
                'code' => 'RPDO-01',
                'error' => $e->getMessage(),
                'resource' => $resource,
                'table' => $this->table,
                'keys' => $this->keys,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpdo01'
            ));
        }
    }


    public function add ($item) {
        // Get the object keys.
        $objData = get_object_vars($item);
        $keys = array_keys($objData);
        // Query time.
        $sql = 'INSERT INTO '.$this->table.' ('.implode(',', $keys).') VALUES (:'.implode(',:', $keys).')';
        $this->query($sql, $objData);
        // Return the item with the primary key if single.
        if (count($this->keys) === 1) {
            $pk = current($this->keys);
            $item->$pk = $this->pdo->lastInsertId();
        }
        return $item;
    }

    public function find ($options = null) {
        // Get the fields to retrieve.
        $fields = $this->fields ? $this->fields : '*';
        // Query time.
        $sql = 'SELECT '.$fields.' FROM '.$this->table.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Fetch the result.
        if ($this->objectName) {
            $statement->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->objectName);
        } else {
            $statement->setFetchMode(PDO::FETCH_ASSOC);
        }
        return $statement->fetchAll();
    }

    public function from ($resource) {
        $this->table = $resource;
    }

    public function modify ($item, $options = null, $pkFilter = true) {
        // Add the primary keys to the query condition.
        if ($pkFilter) {
            $pksRule = $this->getKeysCondition($item);
            $options['condition'].= $options['condition'] ? ' and '.$pksRule : $pksRule;
        }
        // Update query.
        $sql = 'UPDATE '.$this->table.' SET '.$this->toQuery($item, $this->fields).' '.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Return the number of items updated.
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
            throw new ErrorException(__('The query to the persistence has failed.', 'system'), array(
                'code' => 'RPDO-02',
                'error' => $e->getMessage(),
                'query' => $query,
                'helpLink' => 'http://runphp.taosmi.es/faq/rpdo02'
            ));
        }
    }

    public function remove ($item) {
        // Set the primary key option.
        $options = array(
            'condition' => $this->getKeysCondition($item)
        );
        // Delete query.
        $sql = 'DELETE FROM '.$this->table.$this->parseOptions($options);
        $statement = $this->query($sql);
        // Return the number of items deleted.
        return $statement->rowCount();
    }

    public function select ($fields) {
        $this->fields = $fields;
        return $this;
    }

    public function to ($objectName, $pks = null) {
        $this->objectName = $objectName;
        if ($pks) {
            $this->keys = explode(',', $pks);
        }
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


    /**
     * Get a query condition with the primary keys and values for an item.
     *
     * @param  object  $item  An item with the primary keys values.
     * @return string         The primary keys condition for this item.
     */
    private function getKeysCondition ($item) {
        $condition = [];
        foreach ($this->keys as $key) {
            $condition[] = $key.'="'.$item->$key.'"';
        }
        return implode(' and ', $condition);
    }

    /**
     * Transform the options array into a string that can be delivered to the DB.
     * If no options, return an empty string.
     *
     * @param  array   $options  The options (optional).
     * @return string            The options condition.
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
     * Transform an object or an associative array into a separated by comma
     * key - value pairs string. By default, the pair of values will be joined
     * by comma. It is also possible to specify the fields of the would be
     * affected by the process.
     * Example:
     *      Source: array(key => value, key => value);
     *      Result: key='value',key='value'
     *
     * @param  object  $item    A item with public data.
     * @param  string  $fields  The affected fields separated by comma (optional).
     * @param  string  $join    The character in between pair of values (optional).
     * @return string           A separated by comma key - value pairs string.
     */
    private function toQuery ($item, $fields = null, $join = ',') {
        $query = '';
        // Apply the filter criteria if specified.
        $keys = $fields
                ? array_intersect(explode(',', $fields), array_keys(get_object_vars($item)))
                : array_keys(get_object_vars($item));
        // Create the key - value pair string.
        foreach ($keys as $key) {
            $query.= $key.'=\''.$item->$key.'\''.$join;
        }
        return rtrim($query, $join);
    }
}