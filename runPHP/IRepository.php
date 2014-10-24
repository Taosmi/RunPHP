<?php

namespace runPHP;

/**
 * This class defines the interaction with a persistence system by setting a
 * basic CRUD functionality (add, remove, modify and find), a transaction
 * control (init, commit and rollback) and a backup process to be implemented.
 * It is also defined a direct way to access the persistence system with custom
 * queries.
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
interface IRepository {

    /**
     * Initialize a repository with a connection string.
     *
     * @param string  $connection  The repository connection parameters.
     * @throws                     ErrorException if the initialization fails.
     */
    public function __construct ($connection);


    /**
     * Add a new item to the repository. Return the item stored.
     *
     * @param  object  $item  A new item.
     * @return object         The item stored.
     */
    public function add ($item);

    /**
     * Retrieve all the items that matches the options provided. If no options,
     * retrieve all of them.
     *
     * @param  array  $options  A filter criteria (optional).
     * @return array            The found items or an empty array.
     */
    public function find ($options = null);

    /**
     * Set the target persistence resource.
     *
     * @param string  $resource  A persistence resource name.
     */
    public function from ($resource);

    /**
     * Modify the items that matches the options provided with the new data. If
     * no options, modify all of them.
     *
     * @param  object  $item     An item with the new data.
     * @param  array   $options  A filter criteria (optional).
     * @return int               The number of modified items.
     */
    public function modify ($item, $options = null);

    /**
     * Execute a custom query directly to the persistence resource.
     *
     * @param   string  $query   A query.
     * @param   array   $data    The placeholders data if placeholders are used (optional).
     * @return \PDOStatement     The query statement.
     * @throws                   ErrorException if the query fails.
     */
    public function query ($query, $data = null);

    /**
     * Remove the item.
     *
     * @param  object  $item  The item to remove.
     * @return int            The number of removed items.
     */
    public function remove ($item);

    /**
     * Set the fields that will be retrieved by the find method for each item.
     * If no data is specified, the find method will retrieve a default set of
     * data.
     *
     * @param  string  $fields  A separated by comma list of fields.
     * @return IRepository      The repository to chain methods.
     */
    public function select ($fields);

    /**
     * Set the class name for the objects retrieved from the repository. This
     * will be useful when casting the query results.
     *
     * @param string  $objectName  The full class name of the object.
     * @param string  $pk          The entity primary key.
     */
    public function to ($objectName, $pk);

    /**
     * Start a new block of operations. This method is not mandatory when only
     * querying the repository.
     */
    public function beginTransaction ();

    /**
     * Consolidate the changes of the current block of operations. This method
     * is mandatory when the beginTransaction() method was used before.
     */
    public function commit ();

    /**
     * Discard the changes of the current block of operations. This method is
     * not mandatory when only querying the repository.
     */
    public function rollback ();

    /**
     * Make a script as backup of the repository. The script is saved to the
     * resources folder of the webApp with the provided name.
     *
     * @param string  $fileName  The file to save the script (optional).
     * @throws                   ErrorException if there is no table selected.
     */
    public function backup ($fileName = null);
}