<?php

/**
* A basic class to connect to a MySql database and execute given
* commands.  The class uses PDO to bind query paramteres.
*
* @author Avais Sethi
*/

class MySqlAdapter {
    private $_select; 
    private $_insert;
    private $_delete;
    private $_update;
    private $_connection;
    private $_login = array();
    
    /**
     * Creates a MySqlAdapter to store SQL commands and execute them using PDO
     *
     * @access public
     * @param string $host Host name
     * @param string $db DB name to be used
     * @param string $user User name
     * @param string $password User password
     */
    public function __construct($host, $db, $user, $password) {
        $this->_login = array(
            'host' => $host,
            'db' => $db,
            'user' => $user,
            'password' => $password
        );

        $this->_connect();
    }

    /**
     * Upon destruction of object, disconnect from DB
     *
     * @access public
     */
    public function __destruct() {
        $this->_disconnect();
    }
    
    /**
     * Sets the SQL select commands
     *
     * @access public
     * @param string $sql SQL select command
     */
    public function set_select_command($sql) {
        $this->_select = $sql;
    }
    
    /**
     * Sets the SQL update command
     *
     * @access public
     * @param string $sql SQL update command
     */
    public function set_update_command($sql) {
        $this->_update = $sql;
    }
    
    /**
     * Sets the SQL delete command
     *
     * @access public
     * @param string $sql SQL delete command
     */
    public function set_delete_command($sql) {
        $this->_delete = $sql;
    }
    
    /**
     * Sets the SQL insert command
     *
     * @access public
     * @param string $sql SQL insert command
     */
    public function set_insert_command($sql) {
        $this->_insert = $sql;
    }

    /**
     * Sets the SQL insert command
     *
     * @access public
     * @return int last insert id
     */
    public function get_last_insert_id() {
        return $this->_connection->lastInsertId();
    }


    /**
    * Get the current PDO connection
    *
    * @access public
    * @return PDO The current PDO connection
    */
    public function get_connection() {
        return $this->_db;
    }
    
    /**
     * Connects to the MySql database using PDO
     *
     * @access private
     * @return bool TRUE if connection is successful
     */
    private function _connect() {
        $login = $this->_login;
        try {
            
            $db_handle = new PDO('mysql:host=' . $login['host'] . ';
                dbname=' . $login['db'], $login['user'], $login['password']);
     
            //Check for connection error
            if (!$db_handle->errorCode()) {
                $this->_connection = $db_handle;
                return true;
            } else {
                throw new Exception('PDO error - Could not connect to MySQL database.');
            }
        
        } catch (PDOException $e) {
            throw new Exception('PDO error - '.$e->getMessage());
        }
    }
    
    /**
     * Disconnects from the database.
     *
     * @access private
     */
    private function _disconnect() {
        if ( !is_null($this->_connection ) ) $this->_connection = null;       
    }
    
    /**
     * Executes the stored select command and retrieves the results
     *
     * @access public
     * @param array $params Array of parameters to match parameters in query
     * @return array An associative array of the query results or FALSE on error
     */
    public function select($params) {
        if (isset($params) && !is_array($params)) $params = array($params);
        return $this->execute($this->_select, $params, true);
    }
    
    /**
     * Executes the stored update command
     *
     * @access public
     * @param array $params Array of parameters to match parameters in query
     * @return bool FALSE on error
     */
    public function update($params) {
        if (isset($params) && !is_array($params)) $params = array($params);
        return $this->execute($this->_update, $params);
    }
    
    /**
     * Executes the stored insert command
     *
     * @access public
     * @param array $params Array of parameters to match parameters in query
     * @return bool FALSE on error
     */
    public function insert($params) {
        if (isset($params) && !is_array($params)) $params = array($params);
        return $this->execute($this->_insert, $params);
    }
    
    /**
     * Executes the stored delete command
     *
     * @access public
     * @param array $params Array of parameters to match parameters in query
     * @return bool FALSE on error
     */
    public function delete($params) {
        if (isset($params) && !is_array($params)) $params = array($params);
        return $this->execute($this->_delete, $params);
    }
    
    /**
     * Executes a given SQL statement
     *
     * @access public
     * @param string $sql SQL statement to be executed
     * @param array $params Array of parameters to match parameters in query
     * @param bool $is_query TRUE for select commands
     * @return mixed Executing a select command will return an associative array of results
     * All other commands will return TRUE on success
     */
    public function execute($sql, $params, $is_query = false) {
        if (isset($params) && !is_array($params)) $params = array($params);

        $cmd = $this->_connection->prepare($sql);  //prepare statement
    
        if (isset($params)) {
            if ((bool)count(array_filter(array_keys($params), 'is_string'))) {  //check if array is assoc or index
                
                foreach ($params as $field => $value) {
                    $cmd->bindValue($field, $value);  //bind values for assoc array of parameters
                }

                $cmd->execute();  //execute query
            
            } else {
                $cmd->execute($params);  //execute query with index array of parameters
            }
        } else {
            $cmd->execute();  //execute query
        }

        if (  $cmd->errorCode() === '00000' ) {
            return ($is_query ? $cmd->fetchAll(PDO::FETCH_ASSOC) : true);
        } else {
            $error = $cmd->errorInfo();
            throw new Exception('PDO error - '.$error[2]);
        }
        
    }
}
