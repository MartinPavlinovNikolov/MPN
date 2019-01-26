<?php

/**
 *
 * @author Martin Nikolov
 */

namespace MPN\Session;

interface ISession {

    /**
     * 
     * @param type $name
     */
    public function __get($name);

    /**
     * 
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value);

    /**
     * Description: destroy current session
     * if $delete_cookie is set to true, cookie in the client will be deleted
     * @param boolean $delete_cookie
     */
    public function destroySession($delete_cookie);

    public function getSessionId();

    public function saveSession();
}
