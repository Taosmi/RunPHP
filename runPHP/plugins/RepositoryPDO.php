<?php

namespace runPHP\plugins {

    use runPHP\IRepository, runPHP\RunException, runPHP\Logger;
    use PDO, PDOException;

    /**
     * This class implements a repository interface with PDO technology.
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
         * @var string  A PDO object.
         */
        private $pdo;

        /**
         * @var string  A DB table.
         */
        private $table;

        /**
         * @var array  The table primary keys.
         */
        private $keys;

        /**
         * @var string  The fields to retrieve when querying.
         */
        private $fields = '*';

        /**
         * @var string  A full class name to cast from the DB results.
         */
        private $object;

        /**
         * @var number  A maximum number of items to get from the repository.
         */
        private $limit, $offset;


        public function __construct ($dsn, $object, $pks = null) {
            try {
                // Get the DSN connection parameters.
                list($resource, $user, $pwd) = explode(',', $dsn);
                // Get the PDO resource.
                $start = microtime(true);
                $this->pdo = new PDO($resource, $user, $pwd);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                Logger::repo('Connecting to the DDBB (' . $resource . ')', $start);
                // Set the repository configuration.
                $this->query('SET NAMES utf8');
                $this->object = $object;
                $this->table = substr($object, strrpos($object, '\\') + 1);
                // Set the primary keys.
                if ($pks) {
                    // Get primary keys from argument.
                    $this->keys = explode(',', $pks);
                } else {
                    // Get primary keys querying the DB of the table.
                    $pksResult = $this->query('SHOW KEYS FROM ' . $this->table);
                    $pksResult->setFetchMode(PDO::FETCH_COLUMN, 4);
                    $this->keys = $pksResult->fetchAll();
                }
            } catch (PDOException $e) {
                throw new RunException(500, __('The connection to the persistence has failed.', 'system'), array(
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
            // Get the item properties.
            $objData = get_object_vars($item);
            $keys = array_keys($objData);
            // Query time.
            $sql = 'INSERT INTO ' . $this->table . ' (' . implode(',', $keys) . ') VALUES (:' . implode(',:', $keys) . ')';
            $this->query($sql, $objData);
            // Update the item primary key if single before returning it.
/*
            if (count($this->keys) === 1) {
                $pk = current($this->keys);
                $item->$pk = $this->pdo->lastInsertId();
            }
*/
            return $item;
        }

        public function find ($filter = null, $orderBy = null) {
            // Set a basic SQL select query from a filter criteria.
            $sql = 'SELECT ' . $this->fields . ' FROM ' . $this->table;
            if ($filter) {
                $sql .= ' WHERE '.$this->getFilterSQL($filter);
            }
            if ($orderBy) {
                $sql .= ' ORDER BY ' . $orderBy;
            }
            if ($this->limit) {
                $sql .= ' LIMIT '.$this->limit;
            }
            if ($this->offset) {
                $sql .= ' OFFSET '.$this->offset;
            }
            // Query and fetch the result.
            $statement = $this->query($sql);
            if ($this->object) {
                // Fetch the results as objects if it was previously set.
                $statement->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->object);
            } else {
                // Fetch the results as arrays.
                $statement->setFetchMode(PDO::FETCH_ASSOC);
            }
            // Reset the fields.
            $this->fields = '*';
            // Return the query results.
            return $statement->fetchAll();
        }

        public function findOne ($filter = null, $orderBy = null) {
            return current($this->find($filter, $orderBy));
        }

        public function from ($resource) {
            $this->table = $resource;
            return $this;
        }

        public function modify ($item, $filter = array(), $pkFilter = true) {
            // Set a basic update query.
            $sql = 'UPDATE ' . $this->table . ' SET ' . $this->toQuery($item, $this->fields);
            // Add the primary keys filter criteria.
            if ($pkFilter) {
                $filter = array_merge($filter, $this->getKeysFilter($item));
            }
            // Transform the filter criteria into SQL.
            if ($filter) {
                $sql .= ' WHERE '.$this->getFilterSQL($filter);
            }
            // Query time.
            $statement = $this->query($sql);
            // Reset the fields.
            $this->fields = '*';
            // Return the number of items updated.
            return $statement->rowCount();
        }

        public function paginate ($limit, $offset = null) {
            $this->limit = $limit;
            $this->offset = $offset;
        }

        public function query ($query, $data = null) {
            try {
                if ($data) {
                    // If data, use a prepare - execute query.
                    $qStart = microtime(true);
                    $statement = $this->pdo->prepare($query);
                    $statement->execute($data);
                    Logger::repo($query, $qStart);
                } else {
                    // If no data, run a standard query.
                    $qStart = microtime(true);
                    $statement = $this->pdo->query($query);
                    Logger::repo($query, $qStart);
                }
                return $statement;
            } catch (PDOException $e) {
                throw new RunException(500, __('The query to the persistence has failed.', 'system'), array(
                    'code' => 'RPDO-02',
                    'error' => $e->getMessage(),
                    'query' => $query,
                    'helpLink' => 'http://runphp.taosmi.es/faq/rpdo02'
                ));
            }
        }

        public function remove ($item, $filter = array(), $pkFilter = true) {
            // Set a delete query.
            $sql = 'DELETE FROM ' . $this->table;
            // Add the primary keys filter criteria.
            if ($pkFilter) {
                $filter = array_merge($filter, $this->getKeysFilter($item));
            }
            // Transform the filter criteria into SQL.
            if ($filter) {
                $sql .= ' WHERE '.$this->getFilterSQL($filter);
            }
            // Query time.
            $statement = $this->query($sql);
            // Return the number of items deleted.
            return $statement->rowCount();
        }

        public function select ($fields) {
            $this->fields = $fields;
            return $this;
        }

        public function to ($object, $pks = null) {
            $this->object = $object;
            $this->keys = $pks ? explode(',', $pks) : null;
            return $this;
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

        private function getFilterSQL ($filter, $join = ' AND ') {
            if (!$filter) {
                return '';
            }
            $sql = array();
            while (list($key, $value) = each($filter)) {
                switch ($key) {
                    case 'and':
                        $sql[] = $this->getFilterSQL($value);
                        break;
                    case 'or':
                        $sql[] = '('.$this->getFilterSQL($value, ' OR ').')';
                        break;
                    default:
                        $sql[] = $key . $value;
                }
            }
            return implode($join, $sql);
        }

        /**
         * Get a query filter criteria with the primary keys and values for an item.
         *
         * @param  object $item An item with the primary keys values.
         * @return array          The primary keys filter criteria.
         */
        private function getKeysFilter ($item) {
            $filter = array();
            foreach ($this->keys as $key) {
                $filter[$key] = eq($item->$key);
            }
            return $filter;
        }

        /**
         * Transform an object (public variables) or an associative array into a
         * string with key - value pairs. By default, the pair of values will be
         * joined by comma. It is also possible to specify the fields affected by
         * the process.
         *
         * @param  object $item An item with public data.
         * @param  string $fields The affected fields (optional: all by default).
         * @param  string $join The character in between pair of values (optional: comma by default).
         * @return string           A string with key - value pairs.
         */
        private function toQuery ($item, $fields = null, $join = ',') {
            $query = '';
            // Get the keys. Apply the filter criteria if specified.
            $keys = $fields
                ? array_intersect(explode(',', $fields), array_keys(get_object_vars($item)))
                : array_keys(get_object_vars($item));
            // Create the key - value pair string.
            foreach ($keys as $key) {
                $query .= $key . '=\'' . $item->$key . '\'' . $join;
            }
            return rtrim($query, $join);
        }
    }
}

namespace {

    /**
     * Return an equal SQL condition.
     *
     * @param  string  $value  A value to be equal.
     * @return string          An equal condition.
     */
    function eq ($value) {
        return '=\''.$value.'\'';
    }

    /**
     * Return a not equal SQL condition.
     *
     * @param  string  $value  A value to compare.
     * @return string          A not equal condition.
     */
    function ne ($value) {
        return '!=\''.$value.'\'';
    }

    /**
     * Return a greater than SQL condition.
     *
     * @param  number  $value  A value to compare.
     * @return string          A greater than condition.
     */
    function gt ($value) {
        return '> '.$value;
    }

    /**
     * Return a lower than SQL condition.
     *
     * @param  number  $value  A value to compare.
     * @return string          A lower than condition.
     */
    function lt ($value) {
        return '< '.$value;
    }

    /**
     * Return a greater or equal SQL condition.
     *
     * @param  number  $value  A value to compare.
     * @return string          A greater or equal condition.
     */
    function ge ($value) {
        return '>= '.$value;
    }

    /**
     * Return a lower or equal SQL condition.
     *
     * @param  string  $value  A value to compare.
     * @return string          A lower or equal condition.
     */
    function le ($value) {
        return '<= '.$value;
    }

    /**
     * Return a string like SQL condition
     *
     * @param  string  $value  A value to compare.
     * @return string          A string like condition.
     */
    function like ($value) {
        return ' like \''.$value.'\'';
    }
}
