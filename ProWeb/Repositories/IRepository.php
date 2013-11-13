<?php

namespace ProWeb\Repositories;


/** 
 * The Repository Interface defines the interaction with a persistence system 
 * by setting a basic CRUD functionality (add, remove, modify and find), a 
 * transaction control (init, commit and rollback) and a backup process to be 
 * implemented. It is also defined a direct way to access the persistence 
 * system with custom queries.
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
interface IRepository {

    /**
     * Constructor. Checks and parses the Repository connection string.
     * 
     * @param cfg  A string with the repository connection parameters.
     */
    public function __construct ($cfg);


    /**
     * Adds a new item to the repository. Returns the item stored.
     * 
     * @param item  An associative array with the new item data.
     * @return      An associative array with the item stored.
     */
    public function add ($item);

    /**
     * Retrieves all the items that matches the options provided. If no options,
     * retrieves all of them.
     * 
     * @param options  An associative array with the filter criteria (optional).
     * @return         An array with the found items or an empty array.
     */
    public function find ($options = null);

    /**
     * Sets the target repository.
     * 
     * @param repository  A string with the repository name.
     */
    public function from ($repository);

    /**
     * Modifies the items that matches the options provided with the new data. 
     * If no options, modifies all of them.
     * 
     * @param data     An associative array with the new data.
     * @param options  An associative array with the filter criteria (optional).
     * @return         The number of modified items.
     */
    public function modify ($data, $options = null);

    /**
     * Executes a custom query directly to the repository.
     * 
     * @param query  A string with the query.
     * @return       A query result or an associative array if fetched.
     * @throws       ErrorException() if the query fails
     */
     public function query ($query);

    /**
     * Removes the items that matches the options provided. If no options, 
     * removes all of them.
     * 
     * @param options  An associative array with the filter criteria (optional).
     * @return         The number of removed items.
     */
    public function remove ($options = null);

    /**
     * Sets the data that will be retrieved by the find method for each item. 
     * If no data is specified, the find method will retrieve a default set of 
     * data.
     * 
     * @param data  A string with the data to retrieve.
     */
    public function select ($data);

    /**
     * Sets the class name for the objects retrieved from the repository. This 
     * will be useful when casting the query results.
     * 
     * @param objectName  The full class name of the object.
     */
    public function to ($objectName);


    /**
     * Starts a new block of operations. This method is not mandatory when only 
     * querying the repository.
     */
    public function init ();

    /**
     * Consolidates the changes of the current block of operations. This method 
     * is mandatory when the init() method was used before.
     */
    public function commit ();

    /**
     * Discards the changes of the current block of operations. This method is 
     * not mandatory when only querying the repository.
     */
    public function rollback ();

    /**
     * Makes a backup of the repositories. If no repositories, backups all of 
     * them.
     * 
     * @param repositories  A string with the repositories separated by comma (optional).
     * @return              A backup of the repositories.
     */
    public function backup ($repositories = null);
}
?>