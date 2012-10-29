<?php
/** 
 * The DAL (Data Access Layer) interface defines the interaction with a 
 * persistence system by setting a basic CRUD functionality (add, remove, 
 * modify, find and get), a transaction / connection control (init, commit and 
 * rollback) and a backup process to be implemented. It is also defined a 
 * direct way to access the persistence system with custom queries.
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
interface iDAL {

    /**
     * Checks and parses the DAL (Data Access Layer) connection string.
     * 
     * @param cfg  a string with the connection parameters
     */
    public function __construct ($cfg);

    /**
     * Adds a new item to the persistence system.
     * 
     * @param data  an associative array with the new item data
     * @return      a string with the ID for the new item
     */
    public function add ($data);

    /**
     * Retrieves all the items that matches the options provided. If no options,
     * retrieves all of them.
     * 
     * @param options  an associative array with the filter criteria (optional)
     * @return         an array with the found items
     */
    public function find ($options = null);

    /**
     * Sets the target resource into the persistence system.
     * 
     * @param resource  a string with the resource name
     */
    public function from ($resource);

    /**
     * Retrieves an item that matches the unique ID or primary key provided.
     * 
     * @param pk  a string with a unique ID or primary key
     * @return    an associative array with the item data or null if no item is found
     */
    public function get ($pk);

    /**
     * Modifies the items that matches the options provided with the new data. 
     * If no options, modifies all of them.
     * 
     * @param data     an associative array with the new data
     * @param options  an associative array with the filter criteria (optional)
     * @return         the number of modified items
     */
    public function modify ($data, $options = null);

    /**
     * Executes a custom query directly into the persistence system.
     * 
     * @param query  a string with the query
     * @param fetch  a boolean, if true fetches the query result into an array (optional)
     * @return       a query result or an associative array if fetched
     * @throws       DALException() if the query fails
     */
     public function query ($query, $fetch = false);

    /**
     * Removes the items that matches the options provided. If no options, 
     * removes all of them.
     * 
     * @param options  an associative array with the filter criteria (optional)
     * @return         the number of removed items
     */
    public function remove ($options = null);

    /**
     * Sets the data that will be retrieved by the find method for each item. 
     * If no data is  specified, the find method will retrieve a default set of 
     * data.
     * 
     * @param data  a string with the data to retrieve
     */
    public function select ($data);

    /**
     * Starts a new block of operations and establishes a connection to the 
     * persistence system if needed.
     */
    public function init ();

    /**
     * Consolidates the changes of the current block of operations and closes 
     * the connection to the persistence system.
     */
    public function commit ();

    /**
     * Discards the changes of the current block of operations and closes the 
     * connection to the persistence system.
     */
    public function rollback ();

    /**
     * Makes a backup of the entities provided. If no entities, makes a backup 
     * of the whole persistence system.
     * 
     * @param entities  a string with the entities separated by comma (optional)
     * @return          a backup of the entities provided
     */
    public function backup ($entities = null);
}
?>