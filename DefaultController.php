<?php

/**
 * Description: DefaultController
 *
 * @author Martin Nikolov
 */

namespace MPN;

use MPN\App;
use MPN\View;
use MPN\InputData;
use MPN\Validation;

/**
 * @Description: purpose of this class is
 *               to make all important instances
 *               for the controller in main project.
 *               implements shortcuts.
 */
class DefaultController {

    /**
     *
     * @var \MPN\App 
     */
    public $app;

    /**
     *
     * @var \MPN\View
     */
    public $view;

    /**
     *
     * @var \MPN\Config 
     */
    public $config;

    /**
     *
     * @var \MPN\InputData
     */
    public $input;

    /**
     *
     * @var \MPN\Validation
     */
    public $validation;

    /**
     *
     * @var \MPN\NativeSession||DBSession
     */
    public $session;

    public function __construct() {
        $this->app = App::getInstance();
        $this->view = View::getInstance();
        $this->config = $this->app->getConfig();
        $this->input = InputData::getInstance();
        $this->validation = new Validation();
        $this->session = $this->app->getSession();
    }

    /**
     * 
     * @param mixed $json
     */
    public function jsonResponse($json) {

        header('Content-Type: application/json');
        echo json_encode($json);
    }

}
