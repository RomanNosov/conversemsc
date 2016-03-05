<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'shop_instantorder' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'type' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'required' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => array('id'),
        ),
    ),
);
