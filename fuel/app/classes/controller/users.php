<?php

/**
 * Controller for actions on articles
 *
 * @package Controller
 * @created 2018-03-02
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */
class Controller_Users extends \Controller_App {

    /**
     * Get list
     */
    public function action_register() {
        return \Bus\Users_Register::getInstance()->execute();
    }
    
    /**
     * Login
     */
    public function action_login() {
        return \Bus\Users_Login::getInstance()->execute();
    }
    
    /**
     * Products
     */
    public function action_products() {
        return \Bus\Users_Products::getInstance()->execute();
    }
}
