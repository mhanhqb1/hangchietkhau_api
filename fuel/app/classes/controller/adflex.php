<?php

/**
 * Controller_Adflex
 *
 * @package Controller
 * @created 2020-04-30
 * @version 1.0
 * @author AnhMH
 */
class Controller_Adflex extends \Controller_Rest {

    /**
     * Success
     */
    public function action_success() {
        $params = $_GET;
        $params['status'] = \Model_Order::$status['success'];
        \Model_Order::add_from_flex($params);
        echo 'DONE';
        exit();
    }
    
    /**
     * Pending
     */
    public function action_pending() {
        $params = $_GET;
        $params['status'] = \Model_Order::$status['pending'];
        \Model_Order::add_from_flex($params);
        echo 'DONE';
        exit();
    }
    
    /**
     * Cancel
     */
    public function action_cancel() {
        $params = $_GET;
        $params['status'] = \Model_Order::$status['cancel'];
        \Model_Order::add_from_flex($params);
        echo 'DONE';
        exit();
    }
    
    /**
     * Duplicate
     */
    public function action_duplicate() {
        $params = $_GET;
        $params['status'] = \Model_Order::$status['duplicate'];
        \Model_Order::add_from_flex($params);
        echo 'DONE';
        exit();
    }
}
