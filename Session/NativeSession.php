<?php

namespace MPN\Session;

use MPN\Session\ISession;

/**
 * Description: NativeSession
 *
 * @author Martin Nikolov
 */
class NativeSession implements ISession {

    /**
     * 
     * @param string $name
     * @param number $lifetime
     * @param string $path
     * @param string $domain
     * @param bool $secure
     */
    public function __construct($name, $lifetime = 3600, $path = null, $domain = null, $secure = false) {

        if (strlen($name) < 1) {

            $name = '__sess';
        }
        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }

    /**
     * 
     * @param string $name
     * @return array $_SESSION
     */
    public function __get($name) {
        return $_SESSION[$name];
    }

    /**
     * 
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * Description: destroy current session
     * if $delete_cookie is set to true, cookie in the client will be deleted
     * @param type boolean [$delete_cookie]
     */
    public function destroySession($delete_cookie = false) {
        if ($delete_cookie) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * 
     * @return mixed id
     */
    public function getSessionId() {

        return session_id();
    }

    /**
     * @Description: save all changes in the session
     */
    public function saveSession() {

        session_write_close();
    }

}
