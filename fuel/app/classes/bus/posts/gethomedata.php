<?php

namespace Bus;

/**
 * Get list data
 *
 * @package Bus
 * @created 2017-10-29
 * @version 1.0
 * @author AnhMH
 */
class Posts_GetHomeData extends BusAbstract
{
    /** @var array $_required field require */
    protected $_required = array(
        
    );

    /** @var array $_length Length of fields */
    protected $_length = array(
        
    );

    /** @var array $_email_format field email */
    protected $_email_format = array(
        
    );

    /**
     * Call function get_all() from model Product
     *
     * @author AnhMH
     * @param array $data Input data
     * @return bool Success or otherwise
     */
    public function operateDB($data)
    {
        try {
            $this->_response = \Model_Post::get_home_data($data);
            return $this->result(\Model_Post::error());
        } catch (\Exception $e) {
            $this->_exception = $e;
        }
        return false;
    }
}
