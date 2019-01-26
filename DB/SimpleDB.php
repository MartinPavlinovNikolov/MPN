<?php

/**
 * Description of SimpleDB: API for database
 * 
 * @author Martin Nikolov
 */

namespace MPN\DB;

use MPN\App;
use MPN\DB\QueryBilder;

/**
 * this class use PDO-class
 * work with some database
 * you must have correct config file
 */
class SimpleDB {

    /**
     *
     * @param type string 
     */
    protected $connection = 'default';

    /**
     *
     * @param type string
     */
    private $db = null;

    /**
     *
     * @param type mixed
     */
    private $stmt = null;

    /**
     *
     * @param type mixed
     */
    private $params = array();

    /**
     *
     * @param type string db-query
     */
    private $sql;

    /**
     *
     * @var \MPN\DB\QueryBilder
     */
    protected $queryBilder = null;

    /**
     * 
     * @param \PDO $connection
     */
    public function __construct($connection = null) {
        if ($connection instanceof \PDO) {
            $this->db = $connection;
        } else if ($connection != null) {
            $this->db = App::getInstance()->getDBConnection($connection);
            $this->connection = $connection;
        } else {
            $this->db = App::getInstance()->getDBConnection($this->connection);
        }
        /* take query-bilder mechanism */
        //TODO: DI
        $this->queryBilder = new QueryBilder();
    }

    /**
     * 
     * @param type $sql
     * @param type $params
     * @param type $pdoOptions
     * 
     * @return \MPN\DB\SimpleDB
     */
    public function prepare($sql, $params = array(), $pdoOptions = array()) {

        $this->stmt = $this->db->prepare($sql, $pdoOptions);
        $this->params = $params;
        $this->sql = $sql;
        return $this;
    }

    /**
     * 
     * @param type $params
     * 
     * @return \MPN\DB\SimpleDB
     */
    public function execute($params = array()) {

        if ($params) {
            $this->params = $params;
        }

        $this->stmt->execute($this->params);
        return $this;
    }

    /**
     * 
     * @return type array
     */
    public function fetchAllAssoc() {

        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 
     * @return type array
     */
    public function fetchRowAssoc() {

        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 
     * @return type array
     */
    public function fetchAllNum() {

        return $this->stmt->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * 
     * @return type array
     */
    public function fetchRowNum() {

        return $this->stmt->fetch(\PDO::FETCH_NUM);
    }

    /**
     * 
     * @return type object
     */
    public function fetchAllObj() {

        return $this->stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * 
     * @return type object
     */
    public function fetchRowObj() {

        return $this->stmt->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * 
     * @param type $column
     * @return type array
     */
    public function fetchAllColumn($column) {

        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }

    /**
     * 
     * @param type $column
     * @return type array
     */
    public function fetchRowColumn($column) {

        return $this->stmt->fetch(\PDO::FETCH_COLUMN, $column);
    }

    /**
     * 
     * @param type $class
     * @return type object
     */
    public function fetchAllClass($class) {

        return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $class);
    }

    /**
     * 
     * @param type $class
     * @return type object
     */
    public function fetchRowClass($class) {

        return $this->stmt->fetch(\PDO::FETCH_BOUND, $class);
    }

    /**
     * 
     * @return type number
     */
    public function getLastInsertId() {

        return $this->db->lastInsertId();
    }

    /**
     * 
     * @return type number
     */
    public function getAffectedRows() {

        return $this->stmt->rowCount();
    }

    /**
     * 
     * @return type mixed
     */
    public function getSTMT() {

        return $this->stmt;
    }

}
