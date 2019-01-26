<?php

/**
 * Description: JsonRPCRouter
 *
 * @author Martin Nikolov
 */

namespace MPN\Routers;

use MPN\Routers\IRouter;
use Exception;

class JsonRPCRouter implements IRouter {
    /**
     *
     * @var type array
     */
    private $_map = array();
    /**
     *
     * @var type number
     */
    private $_requestId;
    /**
     *
     * @var type array
     */
    private $_post = array();
    /**
     * 
     * @throws \Exception
     */
    public function __construct() {     
        if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/json') {

            throw new Exception('Require json request', 400);
        }
    }
    /**
     * @description: set custom params for this router
     * @param type $ar
     */
    public function setMethodsMap($ar) {

        if (is_array($ar)) {

            $this->_map = $ar;
        }
    }
    /**
     * 
     * @return type PHP array from JSON object
     * @throws \Exception
     */
    public function getURI() {

        if (!is_array($this->_map) || count($this->_map) == 0) {
            $ar = \MPN\App::getInstance()->getConfig()->rpcRoutes;

            if (is_array($ar) && count($ar) > 0) {
                $this->_map = $ar;
            } else {
                throw new Exception('Router require method map', 500);
            }
        }

        header('Content-Type: application/json');
        $request = json_decode(file_get_contents('php://input'), true);
        if (!is_array($request)) {

            throw new Exception('Require json request(must be array!)', 400);
        } else {

            if (!isset($request['method'])) {

                throw new Exception('Require json request(missing "method")', 400);
            } else {
                if ($this->_map[$request['method']]) {
                    $this->_requestId = $request['id'];
                    $this->_post = $request['params'];
                    return $this->_map[$request['method']];
                } else {

                    throw new Exception('Method not found', 501);
                }
            }
        }
    }
    /**
     * 
     * @return type number
     */
    public function getRequestId() {
        return $this->_requestId;
    }
    /**
     * 
     * @return type mixed
     */
    public function getPost() {
        return $this->_post;
    }

}
