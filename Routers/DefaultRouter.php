<?php

/**
 * Description: DefaultRouter
 *
 * @author Martin Nikolov
 */

namespace MPN\Routers;

use MPN\Routers\IRouter;

class DefaultRouter implements IRouter {

    /**
     * 
     * @return type string
     */
    public function getURI() {
        /* if i want http://domain/namespace/controller/method */
        return substr($_SERVER['REQUEST_URI'], 1);

        /* if i want http://domain/filename.php/namespace/controller/method */
        //return substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']) + 1);
    }

    /**
     * 
     * @return type mixed
     */
    public function getPost() {
        return $_POST;
    }

}
