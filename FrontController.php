<?php

namespace MPN;

use MPN\Routers\IRouter;
use MPN\App;
use MPN\InputData;
use MPN\FrontController;
use Exception;

/**
 * @DP: Singleton
 * 
 * @Description: FrontController calling needed routes to do they jobs, call needed controllers, methods and inject the input data.
 *
 * @author Martin Nikolov
 */
class FrontController {

    /**
     *
     * @var \MPN\FrontController
     */
    private static $_instance = null;

    /**
     *
     * @var string
     */
    private $ns = null;

    /**
     *
     * @var object
     */
    private $controller = null;

    /**
     *
     * @var method
     */
    private $method = null;

    /**
     *
     * @var MPN\Rourets\iRouter; 
     */
    private $router = null;

    private function __construct() {
        
    }

    /**
     * 
     * @return \some name space\Router
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * 
     * @param IRouter object $router
     */
    public function setRouter(IRouter $router) {
        $this->router = $router;
    }

    /**
     * @Desription: found router, get input data, found controller, found method, inject the data
     * @throws Exception
     */
    public function dispatch() {
        if ($this->router == null) {
            throw new Exception('No valid router found', 500);
        }
        $_uri = $this->router->getURI();
        $routes = App::getInstance()->getConfig()->routes;
        $_rc = null;
        if (is_array($routes) && count($routes) > 0) {

            foreach ($routes as $k => $v) {

                if (stripos($_uri, $k) === 0 && ($_uri == $k || stripos($_uri, $k . '/') === 0) && $v['namespace']) {
                    $this->ns = $v['namespace'];
                    $_uri = substr($_uri, strlen($k) + 1);
                    $_rc = $v;
                    break;
                }
            }
        } else {
            throw new Exception('Default route missing', 500);
        }
        if ($this->ns == null && $routes['*']['namespace']) {
            $this->ns = $routes['*']['namespace'];
            $_rc = $routes['*'];
        } else if ($this->ns == null && !$routes['*']['namespace']) {
            throw new Exception('Default route missing', 500);
        }

        $input = InputData::getInstance();
        $_params = explode('/', $_uri);

        if ($_params[0]) {
            $this->controller = strtolower($_params[0]);
            //if we do not have controller and method, we do not have params 
            if ($_params[1]) {

                $this->method = strtolower($_params[1]);
                unset($_params[0], $_params[1]);
                $input->setGet(array_values($_params));
            } else {
                $this->method = $this->getDefaultMethod();
            }
        } else {
            $this->controller = $this->getDefaultController();
            $this->method = $this->getDefaultMethod();
        }

        if (is_array($_rc) && $_rc['controllers']) {

            if ($_rc['controllers'][$this->controller]['methods'][$this->method]) {
                $this->method = strtolower($_rc['controllers'][$this->controller]['methods'][$this->method]);
            }
            if ($_rc['controllers'][$this->controller]['to']) {
                $this->controller = strtolower($_rc['controllers'][$this->controller]['to']);
            }
        }
        $input->setPost($this->router->getPost());
        $f = $this->ns . '\\' . ucfirst($this->controller);
        if (!class_exists($f)) {
            throw new Exception("Class with name {$f} do not exist!", 405);
        }
        $newControoler = new $f();
        if (!method_exists($newControoler, $this->method)) {
            throw new Exception("Call to undefined method: someExistingClass->{$this->method}()", 405);
        }
        $newControoler->{$this->method}();
    }

    public function getDefaultController() {
        $controler = App::getInstance()->getConfig()->app['default_controller'];
        if ($controler) {
            return strtolower($controler);
        }
        return 'index';
    }

    public function getDefaultMethod() {
        $method = App::getInstance()->getConfig()->app['default_method'];
        if ($method) {
            return strtolower($method);
        }
        return 'index';
    }

    /**
     * 
     * @return \MPN\FrontController
     */
    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new FrontController();
        }
        return self::$_instance;
    }

}
