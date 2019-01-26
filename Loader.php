<?php

namespace MPN;
use Exception;
/**
 * @Description: auto-load my classes
 *
 * @author Martin Nikolov
 */
final class Loader {

    private static $namespaces = [];

    private function __construct() {
        
    }

    public static function registerAutoLoad() {
        spl_autoload_register(array('MPN\Loader', 'autoload'));
    }

    public static function autoload($class) {
        self::loadClass($class);
    }

    public static function loadClass($class) {
        foreach (self::$namespaces as $key => $value) {

            if (strpos($class, $key) === 0) {

                $file = realpath(substr_replace(str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php', $value, 0, strlen($key)));
                if ($file && is_readable($file)) {

                    include $file;
                } else {
                    throw new Exception("File cannot be included: $class \n 1-check is class exist in your project \n 2-check config file \n 3-check for namespaces in executing file \n or just FUCK THAT SHIT, and take one cold BEER \n", 500);
                }

                break;
            }
        }
    }

    public static function registerNamespace($namespace, $path) {

        $namespace = trim($namespace);

        if (strlen($namespace) > 0) {
            if (!$path) {
                throw new Exception('Invalid path');
            }
            $_path = realpath($path);
            if ($_path && is_dir($_path) && is_readable($_path)) {
                self::$namespaces[$namespace . '\\'] = $_path . DIRECTORY_SEPARATOR;
            } else {

                throw new Exception('Namespace directory read error:' . $path);
            }
        } else {

            throw new Exception('Invalid namespace' . $path);
        }
    }

    public static function registerNamespaces($namespaces) {

        if (is_array($namespaces)) {

            foreach ($namespaces as $key => $value) {
                self::registerNamespace($key, $value);
            }
        } else {
            throw new Exception('Invalid namespaces');
        }
    }

}
