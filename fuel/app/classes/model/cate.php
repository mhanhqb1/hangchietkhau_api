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
class Model_Cate extends Model_Abstract {
    
    /** @var array $_properties field of table */
    protected static $_properties = array(
        'id',
        'name',
        'slug',
        'parent_id',
        'position',
        'created',
        'updated',
        'is_disable'
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
    protected static $_table_name = 'cates';

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
        
        // Check if exist User
        if (!empty($param['id'])) {
            $self = self::find($param['id']);
            if (empty($self)) {
                self::errorNotExist('cate_id');
                return false;
            }
        } else {
            $self = new self;
            $isNew = true;
        }
        
        // Set data
        if (!empty($param['name'])) {
            $self->set('name', $param['name']);
            $self->set('slug', \Lib\Str::convertURL($param['name']));
        }
        if (!empty($param['parent_id'])) {
            $self->set('parent_id', $param['parent_id']);
        }
        if (!empty($param['position'])) {
            $self->set('position', $param['position']);
        }
        $self->set('updated', $time);
        if ($isNew) {
            $self->set('created', $time);
        }
        
        // Save data
        if ($self->save()) {
            if (empty($self->id)) {
                $self->id = self::cached_object($self)->_original['id'];
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
        if (isset($param['parent_id'])) {
            if (empty($param['parent_id'])) {
                $query->where_open();
                $query->where(self::$_table_name.'.parent_id', 'IS', null);
                $query->or_where(self::$_table_name.'.parent_id', 0);
                $query->where_close();
            } else {
                $query->where(self::$_table_name.'.parent_id', $param['parent_id']);
            }
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
            'total' => $total,
            'data' => $data
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
        
        $data = self::find($id);
        if (empty($data)) {
            self::errorNotExist('cate_id');
            return false;
        }
        
        return $data;
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
            $self = self::del(array('id' => $id));
        }
        return true;
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
        if (!empty($param['address'])) {
            $query->where(self::$_table_name.'.address', 'LIKE', "%{$param['address']}%");
        }
        if (!empty($param['tel'])) {
            $query->where(self::$_table_name.'.tel', 'LIKE', "%{$param['tel']}%");
        }
        if (!empty($param['email'])) {
            $query->where(self::$_table_name.'.email', 'LIKE', "%{$param['email']}%");
        }
        if (!empty($param['not_id'])) {
            $query->where(self::$_table_name.'.id', '!=', $param['not_id']);
        }
        if (isset($param['parent_id'])) {
            if (empty($param['parent_id'])) {
                $query->where_open();
                $query->where(self::$_table_name.'.parent_id', 'IS', null);
                $query->or_where(self::$_table_name.'.parent_id', 0);
                $query->where_close();
            } else {
                $query->where(self::$_table_name.'.parent_id', $param['parent_id']);
            }
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
            $query->order_by(self::$_table_name . '.parent_id', 'ASC');
            $query->order_by(self::$_table_name . '.position', 'ASC');
        }
        
        // Get data
        $data = $query->execute()->as_array();
        
        // Get sub cate
        if (!empty($data) && !empty($param['get_sub_cates'])) {
            foreach ($data as &$val) {
                $subCateIds = array();
                foreach ($data as $k => $v) {
                    if ($val['id'] == $v['root_id']) {
                        $val['sub_cates'][] = $v;
                        $subCateIds[] = $v['id'];
                    }
                }
                if (!empty($subCateIds)) {
                    $val['default_articles'] = Model_Post::get_all(array(
                        'cate_id' => $subCateIds,
                        'limit' => 4,
                        'page' => 1
                    ));
                }
            }
        }
        
        return $data;
    }
}
