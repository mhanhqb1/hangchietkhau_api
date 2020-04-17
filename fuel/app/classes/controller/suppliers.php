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
class Controller_Suppliers extends \Controller_App {

    /**
     * Get list
     */
    public function action_list() {
        return \Bus\Suppliers_List::getInstance()->execute();
    }
    
    /**
     * Get list
     */
    public function action_addupdate() {
        return \Bus\Suppliers_AddUpdate::getInstance()->execute();
    }
    
    /**
     * Get list
     */
    public function action_detail() {
        return \Bus\Suppliers_Detail::getInstance()->execute();
    }
    
    /**
     * Disable
     */
    public function action_disable() {
        return \Bus\Suppliers_Disable::getInstance()->execute();
    }
    
    /**
     * Disable
     */
    public function action_all() {
        return \Bus\Suppliers_All::getInstance()->execute();
    }
}
