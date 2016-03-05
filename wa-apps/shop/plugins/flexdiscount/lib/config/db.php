<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
return array(
    'shop_flexdiscount' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'mask' => array('varchar', 15, 'null' => 0),
        'value' => array('varchar', 30, 'null' => 0),
        'discount_percentage' => array('int', 3, 'null' => 0, 'default' => '0'),
        'discount' => array('int', 10, 'null' => 0, 'default' => '0'),
        'affiliate' => array('int', 11, 'null' => 0, 'default' => '0'),
        'affiliate_percentage' => array('int', 3, 'null' => 0, 'default' => '0'),
        'set_id' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'contact_category_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'discounteachitem' => array('int', 1, 'null' => 0, 'default' => '0'),
        'category_id' => array('int', 10, 'null' => 0, 'default' => '0'),
        'type_id' => array('int', 10, 'null' => 0, 'default' => '0'),
        'coupon_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'domain_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'name' => array('varchar', 200, 'null' => 0, 'default' => ''),
        'code' => array('varchar', 50, 'null' => 0, 'default' => ''),
        'expire_datetime' => array('datetime', 'null' => 1, 'default' => null),
        'sort' => array('int', 10, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'm_v_c_t_c_s_c_d' => array('mask', 'value', 'category_id', 'type_id', 'coupon_id', 'set_id', 'contact_category_id', 'domain_id','unique' => 1),
        ),
        ':options' => array('engine' => 'MyISAM')
    ),
    'shop_flexdiscount_coupon' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'code' => array('varchar', 16, 'null' => 0),
        'limit' => array('int', 11, 'null' => 0, 'default' => '-1'),
        'used' => array('int', 11, 'null' => 0, 'default' => '0'),
        'create_datetime' => array('datetime', 'null' => 0),
        'expire_datetime' => array('datetime', 'null' => 1, 'default' => null),
        'comment' => array('text', 'null' => 0),
        'color' => array('varchar', 6, 'null' => 0, 'default' => ''),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'code' => array('code', 'unique' => 1),
        ),
        ':options' => array('engine' => 'MyISAM')
    ),
    'shop_flexdiscount_coupon_order' => array(
        'coupon_id' => array('int', 11, 'null' => 0),
        'order_id' => array('int', 11, 'null' => 0),
        'discount' => array('float', "14,2", 'null' => 0),
        'affiliate' => array('int', 11, 'null' => 0),
        'datetime' => array('datetime', 'null' => 0),
        ':keys' => array(
            'coupon' => array('coupon_id'),
        ),
        ':options' => array('engine' => 'MyISAM')
    ),
    'shop_flexdiscount_settings' => array(
        'field' => array('varchar', 30, 'null' => 0),
        'ext' => array('varchar', 30, 'null' => 0, 'default' => ''),
        'value' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'field_ext' => array('field', 'ext', 'unique' => 1),
        ),
        ':options' => array('engine' => 'MyISAM')
    ),
    'shop_flexdiscount_affiliate' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'order_id' => array('int', 11, 'null' => 0),
        'affiliate' => array('int', 11, 'null' => 0),
        'status' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'order_id'),
        ),
        ':options' => array('engine' => 'MyISAM')
    ),
);
