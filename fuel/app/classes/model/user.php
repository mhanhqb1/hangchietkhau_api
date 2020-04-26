<?php

use Fuel\Core\DB;
use Lib\Util;

/**
 * Any query in Model Version
 *
 * @package Model
 * @created 2017-10-29
 * @version 1.0
 * @author AnhMH
 */
class Model_User extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'name',
        'address',
        'phone',
        'password',
        'email',
        'note',
        'created',
        'updated',
        'disable',
        'is_actived'
    );

    protected static $_observers = array(
        'Orm\Observer_CreatedAt' => array(
            'events'          => array('before_insert'),
            'mysql_timestamp' => false,
        ),
        'Orm\Observer_UpdatedAt' => array(
            'events'          => array('before_update'),
            'mysql_timestamp' => false,
        ),
    );

    /** @var array $_table_name name of table */
    protected static $_table_name = 'users';

    /**
     * Register
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function register($param)
    {
        // Init
        $self = new self;
        $time = time();
        $param['password'] = Util::encodePassword($param['password'], $param['email']);
        
        // Check if duplicate Email
        $check = self::find('first', array(
            'where' => array(
                'email' => $param['email']
            )
        ));
        if (!empty($check)) {
            self::errorDuplicate('email');
            return false;
        }
        
        // Set data
        $self->set('name', $param['name']);
        $self->set('email', $param['email']);
        $self->set('password', $param['password']);
        $self->set('is_actived', 0);
        $self->set('updated', $time);
        $self->set('created', $time);
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            return $self;
        }
        
        return false;
    }
}
