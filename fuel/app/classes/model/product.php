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
class Model_Product extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'name',
        'slug',
        'qty',
        'admin_price',
        'admin_income',
        'wholesale_price',
        'discount',
        'discount_unit',
        'root_price',
        'price',
        'wholesale_income',
        'supplier_id',
        'source_url',
        'source_name',
        'image',
        'image_2',
        'image_3',
        'image_4',
        'image_5',
        'attributes',
        'description',
        'detail',
        'seo_keyword',
        'seo_description',
        'total_view',
        'total_sale',
        'is_hot',
        'is_disable',
        'created',
        'updated',
        'aff_url',
        'aff_news_url',
        'source_pid'
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
    protected static $_table_name = 'products';
    
    public static $_discount_unit = array(
        'percent' => 0,
        'money' => 1
    );

    /**
     * Add update info
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function add_update($param)
    {
        // Init
        $self = array();
        $isNew = false;
        $time = time();
        $adminPrice = !empty($param['admin_price']) ? $param['admin_price'] : 0;
        $wholesalePrice = !empty($param['wholesale_price']) ? $param['wholesale_price'] : 0;
        $adminIncome = $wholesalePrice - $adminPrice;
        $discount = !empty($param['discount']) ? $param['discount'] : 0;
        $discountUnit = !empty($param['discount_unit']) ? $param['discount_unit'] : 0;
        $rootPrice = !empty($param['root_price']) ? $param['root_price'] : 0;
        $discountPrice = $discountUnit == self::$_discount_unit['percent'] ? $rootPrice*$discount/100 : $discount;
        $price = $rootPrice - $discountPrice;
        $wholesaleIncome = $price - $wholesalePrice;
        // Check if exist User
        if (!empty($param['id'])) {
            $self = self::find($param['id']);
            if (empty($self)) {
                self::errorNotExist('product_id');
                return false;
            }
        } else {
            $self = new self;
            $isNew = true;
        }
        
        // Upload image
        if (!empty($_FILES)) {
            $uploadResult = \Lib\Util::uploadImage(); 
            if ($uploadResult['status'] != 200) {
                self::setError($uploadResult['error']);
                return false;
            }
            $param['image'] = !empty($uploadResult['body']['image']) ? $uploadResult['body']['image'] : '';
            $param['image_2'] = !empty($uploadResult['body']['image_2']) ? $uploadResult['body']['image_2'] : '';
            $param['image_3'] = !empty($uploadResult['body']['image_3']) ? $uploadResult['body']['image_3'] : '';
            $param['image_4'] = !empty($uploadResult['body']['image_4']) ? $uploadResult['body']['image_4'] : '';
            $param['image_5'] = !empty($uploadResult['body']['image_5']) ? $uploadResult['body']['image_5'] : '';
        }
        
        // Set data
        $self->set('admin_price', $adminPrice);
        $self->set('admin_income', $adminIncome);
        $self->set('wholesale_price', $wholesalePrice);
        $self->set('wholesale_income', $wholesaleIncome);
        $self->set('discount', $discount);
        $self->set('discount_unit', $discountUnit);
        $self->set('root_price', $rootPrice);
        $self->set('price', $price);
        if (!empty($param['name'])) {
            $self->set('name', $param['name']);
            $self->set('slug', \Lib\Str::convertURL($param['name']));
        }
        if (isset($param['qty'])){
            $self->set('qty', $param['qty']);
        }
        if (isset($param['supplier_id'])) {
            $self->set('supplier_id', $param['supplier_id']);
        }
        if (isset($param['source_url'])) {
            $self->set('source_url', $param['source_url']);
        }
        if (isset($param['source_name'])) {
            $self->set('source_name', $param['source_name']);
        }
        if (!empty($param['image'])) {
            $self->set('image', $param['image']);
        }
        if (!empty($param['image_2'])) {
            $self->set('image_2', $param['image_2']);
        }
        if (!empty($param['image_3'])) {
            $self->set('image_3', $param['image_3']);
        }
        if (!empty($param['image_4'])) {
            $self->set('image_4', $param['image_4']);
        }
        if (!empty($param['image_5'])) {
            $self->set('image_5', $param['image_5']);
        }
        if (isset($param['attributes'])) {
            $self->set('attributes', $param['attributes']);
        }
        if (isset($param['description'])) {
            $self->set('description', $param['description']);
        }
        if (isset($param['detail'])) {
            $self->set('detail', $param['detail']);
        }
        if (isset($param['seo_keyword'])) {
            $self->set('seo_keyword', $param['seo_keyword']);
        }
        if (isset($param['seo_description'])) {
            $self->set('seo_description', $param['seo_description']);
        }
        if (isset($param['is_hot'])) {
            $self->set('is_hot', $param['is_hot']);
        }
        if (isset($param['aff_url'])) {
            $self->set('aff_url', $param['aff_url']);
        }
        if (isset($param['aff_news_url'])) {
            $self->set('aff_news_url', $param['aff_news_url']);
        }
        if (isset($param['source_pid'])) {
            $self->set('source_pid', $param['source_pid']);
        }
        $self->set('updated', $time);
        if ($isNew) {
            $self->set('is_disable', 1);
            $self->set('created', $time);
        }
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
            }
            // Reset post tags
            $delete = self::deleteRow('product_cates', array(
                'product_id' => $self->id
            ));
            if (!empty($param['cate_id'])) {
                $cateIds = explode(',', $param['cate_id']);
                foreach ($cateIds as $c) {
                    Model_Product_Cate::add_update(array(
                        'cate_id' => trim($c),
                        'product_id' => $self->id
                    ));
                }
            }
            return $self->id;
        }
        
        return false;
    }
    
    /**
     * Get list
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_list($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
        ;
                        
        // Filter
        if (!empty($param['name'])) {
            $query->where(self::$_table_name.'.name', 'LIKE', "%{$param['name']}%");
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
        $total = !empty($data) ? DB::count_last_query(self::$slave_db) : 0;
        
        return array(
            'data' => $data,
            'total' => $total
        );
    }
    
    /**
     * Get list
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_user_products($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
//                DB::expr("GROUP_CONCAT(cates.name SEPARATOR ', ') as cate_name")
            )
            ->from(self::$_table_name)
//            ->join('product_cates', 'LEFT')
//            ->on(self::$_table_name.'.id', '=', 'product_cates.product_id')
//            ->join('cates', 'LEFT')
//            ->on('cates.id', '=', 'product_cates.cate_id')  
            ->where(self::$_table_name.'.is_disable', 0)
        ;
                        
        // Filter
        if (!empty($param['name'])) {
            $query->where(self::$_table_name.'.name', 'LIKE', "%{$param['name']}%");
        }
        if (!empty($param['cate_id'])) {
            $query->join('product_cates')
                    ->on('product_cates.product_id', '=', self::$_table_name.'.id')
                    ->where('product_cates.cate_id', $param['cate_id'])
            ;
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
        
        $cates = array();
        if (!empty($param['get_cate'])) {
            $cates = DB::select(
                'cates.*'
            )
            ->from('cates')
            ->execute()
            ->as_array();
        }
        
        return array(
            'data' => $data,
            'cates' => $cates
        );
    }
    
    /**
     * Get detail
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_detail($param)
    {
        $id = !empty($param['id']) ? $param['id'] : '';
        $url = !empty($param['url']) ? $param['url'] : '';
        
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
        ;
        if (!empty($url)) {
            $query->where(self::$_table_name.'.url', $url);
        } else {
            $query->where(self::$_table_name.'.id', $id);
        }
        $data = $query->execute()->offsetGet(0);
        if (empty($data)) {
            self::errorNotExist('product_id');
            return false;
        }
        $cateIds = DB::select(
                'product_cates.cate_id'
            )
            ->from('product_cates')
            ->where('product_id', $data['id'])
            ->execute()
            ->as_array();
        $productCates = array();
        if (!empty($cateIds)) {
            foreach ($cateIds as $t) {
                $productCates[] = $t['cate_id'];
            }
        }
        $data['cate_id'] = $productCates;
        
        return $data;
    }
    
    /**
     * Enable/Disable
     *
     * @author AnhMH
     * @param array $param Input data
     * @return int|bool User ID or false if error
     */
    public static function disable($param)
    {
        $ids = !empty($param['id']) ? $param['id'] : '';
        $disable = !empty($param['disable']) ? $param['disable'] : 0;
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        foreach ($ids as $id) {
//            $self = self::del(array('id' => $id));
            $self = self::find($id);
            if (!empty($self)) {
                $self->set('is_disable', $disable);
                $self->save();
            }
        }
        return true;
    }
    
    /**
     * Delete
     *
     * @author AnhMH
     * @param array $param Input data
     * @return Int|bool
     */
    public static function del($param)
    {
        $delete = self::deleteRow(self::$_table_name, array(
            'id' => $param['id']
        ));
        if ($delete) {
            return $param['id'];
        } else {
            return 0;
        }
    }
    
    /**
     * Get all
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_all($param)
    {
        // Init
        $adminId = !empty($param['admin_id']) ? $param['admin_id'] : '';
        
        if (!empty($param['product_url'])) {
            $cate = Model_Cate::find('first', array(
                'where' => array(
                    'url' => $param['cate_url']
                )
            ));
            if (!empty($cate['id'])) {
                $param['cate_id'] = $cate['id'];
            }
        }
        
        // Query
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.is_disable', 0)
        ;
                        
        // Filter
        if (!empty($param['name'])) {
            $query->where(self::$_table_name.'.name', 'LIKE', "%{$param['name']}%");
        }
        if (!empty($param['cate_id'])) {
            if (!is_array($param['cate_id'])) {
                $param['cate_id'] = explode(',', $param['cate_id']);
            }
            $query->where(self::$_table_name.'.cate_id', 'IN', $param['cate_id']);
        }
        if (isset($param['is_hot']) && $param['is_hot'] != '') {
            $query->where(self::$_table_name.'.is_hot', $param['is_hot']);
        }
        if (isset($param['is_discount']) && $param['is_discount'] != '') {
            $query->where(self::$_table_name.'.discount_price', '>', 0);
        }
        if (isset($param['is_home_slide']) && $param['is_home_slide'] != '') {
            $query->where(self::$_table_name.'.is_home_slide', $param['is_home_slide']);
        }
        if (isset($param['type']) && $param['type'] != '') {
            $query->where(self::$_table_name.'.type', $param['type']);
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
        
        return $data;
    }
    
    /**
     * Get user product detail
     *
     * @author AnhMH
     * @param array $param Input data
     * @return array|bool
     */
    public static function get_user_detail($param)
    {
        $url = !empty($param['slug']) ? $param['slug'] : '';
        
        $query = DB::select(
                self::$_table_name.'.*'
            )
            ->from(self::$_table_name)
            ->where(self::$_table_name.'.slug', $url)
        ;
            
        $data = $query->execute()->offsetGet(0);
        if (empty($data)) {
            self::errorNotExist('product_id');
            return false;
        }
        
        return $data;
    }
}
