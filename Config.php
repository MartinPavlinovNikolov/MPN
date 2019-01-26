<?php

/**
 * Description Configuration class to manage all constants like array
 *
 * @author Martin Nikolov
 */

namespace MPN;

use MPN\Loader;
use MPN\Config;
use Exception;

class Config {

    private static $_instance = null;
    private $_configFolder = null;
    private $_configArray = array();

    private function __construct() {
        
    }

    /**
     * 
     * @param string $configFolder
     * @throws Exception
     */
    public function setConfigFolder($configFolder) {
        if (!$configFolder) {
            throw new Exception('Empty config folder path');
        }

        $_configFolder = realpath($configFolder);
        if ($_configFolder != false && is_dir($_configFolder) && is_readable($_configFolder)) {
            $this->_configArray = array();
            $this->_configFolder = $_configFolder . DIRECTORY_SEPARATOR;
            $namespace = $this->app['namespaces'];
            if (is_array($namespace)) {
                Loader::registerNamespaces($namespace);
            }
        } else {
            throw new Exception('Config directory read error:' . $configFolder);
        }
    }

    /**
     * 
     * @return string path to the config folder
     */
    public function getConfigFolder() {
        return $this->_configFolder;
    }

    /**
     * 
     * @param string $path
     * @throws Exception
     */
    public function includeConfigFile($path) {
        if (!$path) {
            throw new Exception('Invalid argument for includeFunction');
        }
        $_file = realpath($path);
        if ($_file != false && is_file($_file) && is_readable($_file)) {
            $_basename = explode('.php', basename($_file))[0];
            $this->_configArray[$_basename] = include $_file;
        } else {
            throw new Exception('Config file read error' . $path);
        }
    }
/**
 * 
 * @param string $name
 * @return array
 */
    public function __get($name) {
        if (!array_key_exists($name, $this->_configArray)) {
            $this->includeConfigFile($this->_configFolder . $name . '.php');
        }
        if (array_key_exists($name, $this->_configArray)) {
            return $this->_configArray[$name];
        }
        return null;
    }

    /**
     * 
     * @return \MPN\Config
     */
    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }

}
