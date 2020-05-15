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
class Model_Order extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'product_id',
        'user_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'qty',
        'wholesale_income',
        'price',
        'admin_price',
        'admin_income',
        'wholesale_price',
        'root_price',
        'discount',
        'discount_unit',
        'status',
        'ship_cost',
        'note',
        'created',
        'updated',
        'source_oid',
        'source_payout',
        'is_pay',
        'income_id'
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
    protected static $_table_name = 'orders';
    
    public static $status = array(
        'success' => 1,
        'pending' => 0,
        'cancel' => 3,
        'duplicate' => 2,
        'tmp_success' => 4
    );
    
    /**
     * Delete
     *
     * @author AnhMH
     * @param array $param Input data
     * @return Int|bool
     */
    public static function add_from_flex($param)
    {
        // init
        $sourceOID = !empty($param['conversion_id']) ? $param['conversion_id'] : '';
        $sourcePID = !empty($param['offer_id']) ? $param['offer_id'] : '';
        $userId = !empty($param['aff_sub1']) ? $param['aff_sub1'] : 0;
        $sourcePayout = !empty($param['payout']) ? $param['payout'] : '';
        $status = $param['status'];
        $time = time();
        
        // Validate
        if (empty($sourceOID) || empty($sourcePID)) {
            return false;
        }
        
        // Check order exist
        $self = self::find('first', array(
            'where' => array(
                'source_oid' => $sourceOID
            )
        ));
        // Update order
        if (!empty($self)) {
            $self->set('updated', $time);
            $self->set('status', $status);
            $self->save();
            return true;
        }
        
        // Created order
        $self = new self;
        
        // Get product info
        $product = Model_Product::find('first', array(
            'where' => array(
                'source_pid' => $sourcePID
            )
        ));
        if (empty($product)) {
            return false;
        }
        
        $self->set('created', $time);
        $self->set('status', $status);
        $self->set('source_oid', $sourceOID);
        $self->set('product_id', $product['id']);
        $self->set('user_id', $userId);
        $self->set('qty', 1);
        $self->set('wholesale_income', $product['wholesale_income']);
        $self->set('price', $product['price']);
        $self->set('admin_price', $product['admin_price']);
        $self->set('admin_income', $product['admin_income']);
        $self->set('wholesale_price', $product['wholesale_price']);
        $self->set('root_price', $product['root_price']);
        $self->set('discount', $product['discount']);
        $self->set('discount_unit', $product['discount_unit']);
        $self->set('note', 'Order from Adflex');
        $self->set('source_payout', $sourcePayout);
        
        $self->save();
        return true;
    }
    
    /**
     * Get list
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_user_orders($param)
    {
        // Query
        $query = DB::select(
                self::$_table_name.'.*',
                array('products.name', 'product_name'),
                array('products.image', 'product_image')
            )
            ->from(self::$_table_name)
            ->join('products', 'LEFT')
            ->on('products.id', '=', self::$_table_name.'.product_id')
        ;
                        
        // Filter
        if (!empty($param['user_id'])) {
            $query->where(self::$_table_name.'.user_id', $param['user_id']);
        }
        if (!empty($param['product_id'])) {
            $query->where(self::$_table_name.'.product_id', $param['product_id']);
        }
        if (isset($param['status']) && $param['status'] != '') {
            $query->where(self::$_table_name.'.status', $param['status']);
        }
        
        if (isset($param['disable']) && $param['disable'] != '') {
            $disable = !empty($param['disable']) ? 1 : 0;
            $query->where(self::$_table_name.'.is_disable', $disable);
        }
        
        // Pagination
        if (!empty($param['page']) && $param['limit']) {
            $offset = ($param['page'] - 1) * $param['limit'];
            $query->limit($param['limit'])->offset($offset);
        }
        
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
        
        $products = array();
        if (!empty($param['get_products'])) {
            $products = Model_Product::get_all(array());
        }
        
        return array(
            'data' => $data,
            'products' => $products
        );
    }
}
