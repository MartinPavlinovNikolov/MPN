<?php

namespace MPN;

use MPN\Comon;
use MPN\InputData;

/**
 * Description: Manage all incoming data from deferent sources
 *
 * @author Martin Nikolov
 */
class InputData {

    private static $_instance = null;

    /**
     *
     * @var array
     */
    private $_get = array();

    /**
     *
     * @var array
     */
    private $_post = array();

    /**
     *
     * @var array
     */
    private $_cookies = array();

    /**
     * @Description: instantly set cookie
     */
    private function __construct() {

        $this->_cookies = $_COOKIE;
    }

    /**
     * @Description: put mixed data in the post
     * @param array $ar
     */
    public function setPost($ar) {

        if (is_array($ar)) {
            $this->_post = $ar;
        }
    }

    /**
     *      
     * @Description: put mixed data in the get
     * @param type $ar
     */
    public function setGet($ar) {

        if (is_array($ar)) {
            $this->_get = $ar;
        }
    }

    /**
     * description: set cookie manual
     * 
     * @param type $ar
     */
    public function setCookie($ar) {

        if (\is_array($ar)) {
            $this->_cookies = $ar;
        }
    }

    public function hasGet($id) {
        return array_key_exists($id, $this->_get);
    }

    public function hasPost($name) {
        return array_key_exists($name, $this->_post);
    }

    public function hasCookie($name) {
        return array_key_exists($name, $this->_cookies);
    }

    public function get($id, $normalize = null, $default = null) {
        if ($this->hasGet($id)) {

            if ($normalize != null) {
                return Comon::normalize($this->_get[$id], $normalize);
            }
            return $this->_get[$id];
        }
        return $default;
    }

    public function post($name, $normalize = null, $default = null) {
        if ($this->hasPost($name)) {

            if ($normalize != null) {
                return Comon::normalize($this->_post[$name], $normalize);
                }

            return $this->_post[$name];
        }
        return $default;
    }

    /**
     * 
     * @param string $name
     * @param array-conditions $normalize
     * @param type $default
     * @return cookie[$name]
     */
    public function cookie($name, $normalize = null, $default = null) {
        if ($this->hasCookie($name)) {

            if ($normalize != null) {
                return Comon::normalize($this->_cookies($name), $normalize);
            }
            return $this->_cookies[$name];
        }
        return $default;
    }

    /**
     * 
     * @return MPN\Secreary;
     */
    public static function getInstance() {

        if (self::$_instance === null) {
            self::$_instance = new InputData();
        }
        return self::$_instance;
    }

}
