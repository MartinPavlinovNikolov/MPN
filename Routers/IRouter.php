<?php

namespace MPN\Routers;

/**
 * Description every router must have getURI() and getPost() methods!
 *
 * @author Martin Nikolov
 */
interface IRouter {

    public function getURI();
    public function getPost();
}
