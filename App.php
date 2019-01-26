<?php

/**
 * @Description: single entry point
 * @author Martin Nikolov
 */

namespace MPN;

include ('Loader.php');

use MPN\Loader;
use MPN\Config;
use MPN\FrontController;
use MPN\Routers\IRouter;
use MPN\Routers\JsonRPCRouter;
use MPN\Routers\DefaultRouter;
use MPN\Session\NativeSession;
use MPN\Session\DBSessions;
use MPN\Session\ISession;
use MPN\App;
use MPN\View;
use MPN\Common;
use Exception;

/**
 * DP: Singleton
 * this class provide linear application functionality.
 *      step 1:  set exception handler mechanism;
 *  
 *      step 2: registered name space for the framework MPN;
 * 
 *      step 3: turn-on mechanism for autoload classes;
 * 
 *      step 4: take configuration file for extras;
 *              (session, database-session, display exception and so on...);
 * 
 *      step 5: make run function to evaluate configuration setup:
 * 
 *              step 5-1: run configuration file;
 *              step 5-2: run the right controller; 
 *              step 5-3: run native session or database session;
 * 
 *      step 6: on destruct, if has session, saved it;
 * 
 */
class App {

    /**
     *
     * @var \MPN\App
     */
    private static $_instance = null;

    /**
     *
     * @var \MPN\Config
     */
    private $_config = null;

    /**
     *
     * @var \MPN\Router\*
     */
    private $router = null;

    /**
     *
     * @var \MPN\DB\SimpleDB
     */
    private $_dbConnections = array();

    /**
     *
     * @var \MPN\Session\*
     */
    private $_session = null;

    /**
     *
     * @var \MPN\FrontController
     */
    private $_frontController = null;

    private function __construct() {
        set_exception_handler(array($this, '_exceptionHandler'));
        Loader::registerNamespace('MPN', dirname(__FILE__ . DIRECTORY_SEPARATOR));
        Loader::registerAutoLoad();
        $this->_config = Config::getInstance();

        //if config folder is not set, use defaultone
        if ($this->_config->getConfigFolder() == null) {
            $this->setConfigFolder('../config');
        }
    }

    /**
     * give it some path to PHP file, that contain array with settings
     * @param string
     */
    public function setConfigFolder($path) {
        $this->_config->setConfigFolder($path);
    }

    /**
     * take configuration folder name.
     * 
     * @return string $this->_configFolder
     */
    public function getConfigFolder() {
        return $this->_configFolder;
    }

    /**
     * take PHP file content array with default settings of MPN framework.
     * will return configuration like associative  array.
     * @return \MPN\Config
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * 
     * @return string $this->router
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * set some router class like a string
     * @param string $router
     */
    public function setRouter($router) {
        $this->router = $router;
    }

    /**
     * make this baby do some work ;)
     * all pieces are ready to go like one big organism
     * @throws Exception
     */
    public function run() {

        /* if config folder is not set, use defaultone */
        if ($this->_config->getConfigFolder() == null) {

            $this->setConfigFolder('../config');
        }
        $this->_frontController = FrontController::getInstance();
        /* manage custom routers */
        if ($this->router instanceof IRouter) {
            $this->_frontController->setRouter($this->router);
        } else if ($this->router == 'JsonRPCRouter') {
            $this->_frontController->setRouter(new JsonRPCRouter());
        } else if ($this->router == 'CLIRouter') {
            //TODO fix it when CLI is done
            $this->_frontController->setRouter(new DefaultRouter());
        } else {
            $this->_frontController->setRouter(new DefaultRouter());
        }
        /* manage sessions if session if turned "true" in the config file "app" */
        $_sess = $this->_config->app['session'];
        if (isset($_sess['autostart']) && $_sess['autostart']) {

            if ($_sess['type'] == 'native') {

                $_s = new NativeSession($_sess['name'], $_sess['lifetime'], $_sess['path'], $_sess['domain'], $_sess['secure']);
            } else if ($_sess['type'] == 'database') {

                $_s = new DBSessions($_sess['dbConnection'], $_sess['name'], $_sess['dbTable'], $_sess['lifetime'], $_sess['path'], $_sess['domain'], $_sess['secure']);
            } else {

                throw new Exception('No valid session in config file', 500);
            }
            $this->setSession($_s);
        }

        $this->_frontController->dispatch();
    }

    /**
     * 
     * @param ISession object $session
     */
    public function setSession(ISession $session) {

        $this->_session = $session;
    }

    /**
     * 
     * @return ISession object
     */
    public function getSession() {

        return $this->_session;
    }

    /**
     * 
     * @param string $connection
     * @return object \PDO
     * @throws Exception
     */
    public function getDBConnection($connection = 'default') {

        if (!isset($connection)) {

            throw new Exception('No connection identifire providet', 500);
        }
        if (isset($this->_dbConnections[$connection])) {
            return $this->_dbConnections[$connection];
        }
        $_cnf = $this->getConfig()->database;
        if (!isset($_cnf[$connection])) {

            throw new Exception('No valid connection identifire is provied', 500);
        }
        $dbh = new \PDO($_cnf[$connection]['connection_uri'], $_cnf[$connection]['username'], $_cnf[$connection]['pass'], $_cnf[$connection]['pdo_options']);
        $this->_dbConnections[$connection] = $dbh;
        return $dbh;
    }

    /**
     * 
     * @return \MPN\App
     */
    public static function getInstance() {

        if (self::$_instance == null) {

            self::$_instance = new App();
        }
        return self::$_instance;
    }

    /**
     * 
     * @param Exception $ex
     */
    public function _exceptionHandler($ex) {
        file_put_contents($this->_config->app['errors_log_location'], $this->errorLogFormating($ex), FILE_APPEND);
        if ($this->_config && $this->_config->app['displayExceptions'] === true) {
            echo '<pre>' . print_r($ex, true) . '</pre>';
        } else {
            $this->displayError($ex->getCode());
        }
    }

    /**
     * @Description: try to display page 404 not found
     *               in fail: display error number in the browser
     * @param Exception $error
     */
    public function displayError($error) {
        try {
            $view = View::getInstance();
            $view->display('errors.404');
        } catch (\Exception $exc) {
            Common::headerStatus($error);
            echo '<h1>' . $error . '</h1>';
            exit;
        }
    }

    public function errorLogFormating($error) {
        $time = date('d/m/Y H:i:s', time() + 3600);
        if ($error instanceof \Exception) {
            $type = 'Exception';
        } else {
            $type = '    Error';
        }
        $trace = str_replace("#", "                trace >> ", $error->getTraceAsString());
        $string = "{$type} is hapen on >> {$time}"
                . "\n              in FILE >> {$error->getFile()}, "
                . "\n              on LINE >> {$error->getLine()}, "
                . "\n            with CODE >> {$error->getCode()}, "
                . "\n          and MESSAGE >> {$error->getMessage()}"
                . "\n{$trace} \n\n\n";
        return $string;
    }

    /**
     * @Description: if has session, save it and close the application
     */
    public function __destruct() {

        if ($this->_session != null) {

            $this->_session->saveSession();
        }
    }

}
