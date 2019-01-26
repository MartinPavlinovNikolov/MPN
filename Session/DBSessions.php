<?php

/**
 * Description:  DBSessions work with MySQL for all customers
 *
 * @author Martin Nikolov
 */

namespace MPN\Session;
use MPN\DB\SimpleDB;
use MPN\Session\ISession;

class DBSessions extends SimpleDB implements ISession {
    /**
     *
     * @var string
     */
    private $sessionName;
    /**
     *
     * @var string
     */
    private $tableName;
    /**
     *
     * @var number
     */
    private $lifetime;
    /**
     *
     * @var string
     */
    private $path;
    /**
     *
     * @var string
     */
    private $domain;
    /**
     *
     * @var bool
     */
    private $secure;
    /**
     *
     * @var string
     */
    private $sessionId = null;
    /**
     *
     * @var array
     */
    private $sessionData = array();
    /**
     * 
     * @param string $dbConnection
     * @param string $name
     * @param string $tableName
     * @param number $lifetime
     * @param string $path
     * @param string $domain
     * @param bool $secure
     */
    public function __construct($dbConnection, $name, $tableName = 'session', $lifetime = 3600, $path = null, $domain = null, $secure = false) {
        
        parent::__construct($dbConnection);
        $this->sessionName = $name;
        $this->tableName = $tableName;
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->sessionId = $_COOKIE[$name];

        if(rand(0, 50)==1){
            $this->_gc();
        }
        
        if (strlen($this->sessionId) < 32) {

            $this->_startNewSession();
        } else if (!$this->_validateSession()) {

            $this->_startNewSession();
        }
    }

    public function _startNewSession() {
        $this->sessionId = md5(uniqid('MPN', true));
        $this->prepare('INSERT INTO ' . $this->tableName . ' (sessid,valid_until) VALUES(?,?)', array($this->sessionId, (time() + $this->lifetime)))->execute();
        setcookie($this->sessionName, $this->sessionId, (time() + $this->lifetime), $this->path, $this->domain, $this->secure, true);
    }
    /**
     * 
     * @return bool
     */
    private function _validateSession() {

        if ($this->sessionId) {

            $d = $this->prepare('SELECT * FROM ' . $this->tableName . ' WHERE sessid=? AND valid_until<=?', array($this->sessionId, (time() + $this->lifetime)))->execute()->fetchAllAssoc();
            if (is_array($d) && count($d) == 1 && $d[0]) {

                $this->sessionData = unserialize($d[0]['sess_data']);
                return true;
            }
        }
        return false;
    }
    /**
     * 
     * @param string $name
     * @return array-value
     */
    public function __get($name) {

        return $this->sessionData[$name];
    }
    /**
     * 
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {

        $this->sessionData[$name] = $value;
    }
    
    public function destroySession() {

        if ($this->sessionId) {
            $this->prepare('DELETE FROM ' . $this->tableName . ' WHERE sessid=?', array($this->sessionId))->execute();
        }
    }
    /**
     * 
     * @return string
     */
    public function getSessionId() {

        return $this->sessionId;
    }

    public function saveSession() {

        if ($this->sessionId) {

            $this->prepare('UPDATE ' . $this->tableName . ' SET sess_data=?,valid_until=? WHERE sessid=?', array(serialize($this->sessionData), (time() + $this->lifetime), $this->sessionId))->execute();
            setcookie($this->sessionName, $this->sessionId, (time() + $this->lifetime), $this->path, $this->domain, $this->secure, true);
        }
        
    }
    /**
     * @Description: call the "garbage-collector" in database
     */
    public function _gc(){
        
        $this->prepare('DELETE FROM `' . $this->tableName . '` WHERE valid_until<?', array(time()))->execute();
    }

}
