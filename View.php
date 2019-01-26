<?php

namespace MPN;

use MPN\View;
use MPN\App;
use Exception;

/**
 * Description: View
 *
 * @author Martin Nikolov
 */
class View {

    private static $_instance = null;
    private $_viewPath = null;
    private $_viewDir = null;
    private $_extension = '.php';
    private $_layoutParts = array();
    private $_layoutData = array();
    private $_data = array();

    private function __construct() {
        $this->_viewPath = App::getInstance()->getConfig()->app['viewsDirectory'];
        if ($this->_viewPath == null) {

            $this->_viewPath = realpath('../views/');
        }
    }

    public function appendToLayouts($name, $template) {
        if ($name && $template) {
            $this->_layoutParts[$name] = $template;
        } else {
            throw new Exception('Layout expect valid key and template', 500);
        }
    }

    public function getLayoutData($name) {
        return $this->_layoutData[$name];
    }

    public function _includeFile($file) {
        if ($this->_viewDir == null) {
            $this->setViewDirectory($this->_viewPath);
        }
        $_fl = $this->_viewDir . str_replace('.', DIRECTORY_SEPARATOR, $file) . $this->_extension;
        if (file_exists($_fl) && is_readable($_fl)) {
            ob_start();
            include $_fl;
            return ob_get_clean();
        } else {

            throw new Exception('File cannot be included', 500);
        }
    }

    public function display($name, $data = array(), $returnAsString = false) {
        if (is_array($data)) {
            $this->_data = array_merge($this->_data, $data);
        }

        if (count($this->_layoutParts) > 0) {
            foreach ($this->_layoutParts as $key => $value) {
                $r = $this->_includeFile($value);
                if ($r) {
                    $this->_layoutData[$key] = $r;
                }
            }
        }
        if ($returnAsString) {
            return $this->_includeFile($name);
        } else {
            echo $this->_includeFile($name);
        }
    }

    public function setViewDirectory($path) {
        $path = trim($path);
        if ($path) {
            $path = \realpath($path) . DIRECTORY_SEPARATOR;
            if (is_dir($path) && is_readable($path)) {

                $this->_viewDir = $path;
            } else {

                throw new Exception('View path is incorect', 500);
            }
        } else {
            throw new Exception('View path is incorect', 500);
        }
    }

    /**
     * @param type $name some key
     * @return type data from buffer
     * @return null if not existing data(templates will not be broken!)
     */
    public function __get($name) {
        if (!array_key_exists($name, $this->_data)) {
            return null;
        }
        return $this->_data[$name];
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public static function getInstance() {

        if (self::$_instance == null) {

            self::$_instance = new View();
        }
        return self::$_instance;
    }

}
