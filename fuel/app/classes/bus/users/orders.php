<?php

namespace Bus;

/**
 * Add/update info
 *
 * @package Bus
 * @created 2017-10-29
 * @version 1.0
 * @author AnhMH
 */
class Users_Orders extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array();

    /** @var array $_length Length of fields */
    protected $_length = array();

    /** @var array $_email_format field email */
    protected $_email_format = array();

    /**
     * Call function add_update() from model User
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Order::get_user_orders($data);
            return $this->result(\Model_Order::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
