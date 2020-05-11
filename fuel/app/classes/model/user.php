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
    
    /**
     * Login
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function login($param)
    {
        // Init
        $param['password'] = Util::encodePassword($param['password'], $param['email']);
        
        // Check if duplicate Email
        $user = self::find('first', array(
            'where' => array(
                'email' => $param['email'],
                'password' => $param['password']
            )
        ));
        if (empty($user)) {
            self::errorNotExist('email');
            return false;
        }
        
        return $user;
    }
    
    /**
     * Get dashboard info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_dashboard($param)
    {
        # Init
        $data = array();
        
        # Get products
        $products = Model_Product::get_all(array(
            'page' => 1,
            'limit' => 10,
            'sort' => 'is_hot-desc'
        ));
        $data['products'] = $products;
        
        $productCount = DB::select('*')->from('products')->where('is_disable', 0)->execute();
        $data['product_cnt'] = count($productCount);
        
        # Get orders
        $wholesaleIncome = 0;
        $orderCount = DB::select('*')->from('orders')->where('user_id', $param['user_id'])->execute()->as_array();
        $data['order_cnt'] = count($orderCount);
        if (!empty($orderCount)) {
            foreach ($orderCount as $v) {
                if ($v['status'] == 1) {
                    $wholesaleIncome += $v['wholesale_income'];
                }
            }
        }
        $data['wholesale_income'] = $wholesaleIncome;
        
        $data['orders'] = DB::select(
                'orders.*',
                array('products.name', 'product_name'),
                array('products.image', 'product_image')
            )
            ->from('orders')
            ->join('products', 'LEFT')
            ->on('products.id', '=', 'orders.product_id')
            ->where('orders.user_id', $param['user_id'])
            ->order_by('orders.created', 'DESC')
            ->limit(10)
            ->execute()
            ->as_array()
        ;
        
        return $data;
    }
}
