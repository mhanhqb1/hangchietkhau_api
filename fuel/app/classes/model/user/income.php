<?php

use Fuel\Core\DB;

/**
 * Any query in Model Version
 *
 * @package Model
 * @created 2017-10-29
 * @version 1.0
 * @author AnhMH
 */
class Model_User_Income extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'user_id',
        'income',
        'created',
        'updated',
        'status'
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
    protected static $_table_name = 'user_incomes';
    
    public static $status = array(
        'success' => 1,
        'pending' => 0,
        'error' => 2
    );

    /**
     * Get all
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function get_all($param)
    {
        $userId = !empty($param['login_user_id']) ? $param['login_user_id'] : 0;
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.user_id', $userId)
        ;
        
        // Sort
        if (!empty($param['sort'])) {
            if (!self::checkSort($param['sort'])) {
                self::errorParamInvalid('sort');
                return false;
            }

            $sortExplode = explode('-', $param['sort']);
            if ($sortExplode[0] == 'created') {
                $sortExplode[0] = self::$_table_name . '.created';
            }
            $query->order_by($sortExplode[0], $sortExplode[1]);
        } else {
            $query->order_by(self::$_table_name . '.created', 'DESC');
        }
        
        // Get data
        $data = $query->execute()->as_array();
        
        return $data;
    }
    
    /**
     * Add user income
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_user_income()
    {
        # Init
        $data = array();
        $minIncome = 500000;
        
        # Get orders
        $orders = Model_Order::find('all', array(
            'where' => array(
                'status' => Model_Order::$status['success'],
                'is_pay' => 0
            )
        ));
        if (!empty($orders)) {
            foreach ($orders as $o) {
                if (empty($data[$o['user_id']])) {
                    $data[$o['user_id']] = array(
                        'income' => 0,
                        'orders' => array()
                    );
                }
                $data[$o['user_id']]['income'] += $o['wholesale_income'];
                $data[$o['user_id']]['orders'][] = $o;
                $o->set('is_pay', 1);
                $o->save();
            }
            
            # Add income
            foreach ($data as $k => $v) {
                if ($v['income'] < $minIncome) {
                    continue;
                }
                $incomeId = self::add_update(array(
                    'user_id' => $k,
                    'income' => $v['income']
                ));
                foreach ($v['orders'] as $o) {
                    $o->set('income_id', $incomeId);
                    $o->save();
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Add update
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_update($param)
    {
        # Init
        $self = new self;
        $time = time();
        
        # Set data
        $self->set('user_id', $param['user_id']);
        $self->set('income', $param['income']);
        $self->set('created', $time);
        $self->set('updated', $time);
        $self->set('status', self::$status['pending']);
        
        # Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            return $self->id;
        }
        
        return true;
    }
}
