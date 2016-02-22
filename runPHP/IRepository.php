<?php

namespace runPHP;

/**
 * A repository is an interaction with a data store system by setting a basic
 * CRUD functionality (add, remove, modify and find) and a transaction control
 * (init, commit and rollback) to be implemented. It is also defined a direct
 * way to access the persistence system with custom queries.
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
     * Initialize a repository  with a DSN.
     *
     * @param  string  $dsn     A repository connection parameters.
     * @param  string  $object  A full class name of the repository items.
     * @param  string  $pks     A separated by comma list of primary keys (optional).
     * @throws RunException     If the initialization fails.
     */
    public function __construct ($dsn, $object, $pks = null);

    /**
     * Add a new item to the repository. Return the item stored.
     *
     * @param  object  $item  A new item.
     * @return object         The item stored.
     */
    public function add ($item);

    /**
     * Retrieve all the items that matches a filter criteria. If no filter
     * criteria, retrieve all of the repository items.
     *
     * @param  array  $filter   A filter criteria (optional).
     * @param  array  $orderBy  An order criteria (optional).
     * @return array            The matched items or an empty array.
     */
    public function find ($filter = null, $orderBy = null);

    /**
     * Retrieve an item that match a filter criteria. If more than one item
     * matches the filter criteria, retrieve the first one.
     *
     * @param  array  $filter  A filter criteria (optional).
     * @param  array  $orderBy  An order criteria (optional).
     * @return object          A matched item or null.
     */
    public function findOne ($filter = null, $orderBy = null);

    /**
     * Set the target resource of the repository.
     *
     * @param string  $resource  A resource name available on the repository.
     * @return IRepository       The repository (chaining method).
     */
    public function from ($resource);

    /**
     * Modify the items that matches a filter criteria. By default, the primary
     * keys of the object will be automatically added to the filter criteria when
     * pkFilter is true (useful when modifying one item).
     *
     * @param  object   $item      An item with the modified data.
     * @param  array    $filter    A filter criteria (optional).
     * @param  boolean  $pkFilter  Add primary keys to the filter criteria when true.
     * @return int                 Number of modified items.
     */
    public function modify ($item, $filter = null, $pkFilter = true);

    /**
     * Execute a custom query directly to the repository.
     *
     * @param   string  $query   A query.
     * @param   array   $data    A placeholders data if placeholders are used (optional).
     * @return \PDOStatement     A query statement.
     * @throws  RunException     If the query fails.
     */
    public function query ($query, $data = null);

    /**
     * Remove an item from the repository. Primary keys will be used to identify
     * the item on the repository, so they must have values.
     *
     * @param  object  $item  An item to be removed.
     * @return int            Number of removed items.
     */
    public function remove ($item);

    /**
     * Set the fields that will be retrieved by the find method for each item
     * and the fields that will be updated by the modify method. If no fields
     * are specified, all the data available for the items will be used.
     *
     * @param  string  $fields  A separated by comma list of fields.
     * @return IRepository      The repository (chaining method).
     */
    public function select ($fields);

    /**
     * Set the class name for the items retrieved as objects from the repository.
     *
     * @param string  $object  A full class name of an object.
     * @param string  $pks     A separated by comma list of primary keys (optional).
     * @return IRepository     The repository (chaining method).
     */
    public function to ($object, $pks = null);

    /**
     * Start a new block of operations. This method is not mandatory when only
     * querying the repository.
     */
    public function beginTransaction ();

    /**
     * Consolidate the changes of the current block of operations. This method
     * is mandatory when the beginTransaction() method was used before and the
     * changes must be saved to the repository.
     */
    public function commit ();

    /**
     * Discard the changes of the current block of operations. This method
     * is mandatory when the beginTransaction() method was used before and the
     * changes must not be saved to the repository.
     */
    public function rollback ();
}