<?php
return array(
    'shop_referrals_activity' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'create_datetime' => array('datetime', 'null' => 0),
        'code' => array('int', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'referer' => array('varchar', 255, 'null' => 0),
        'referer_host' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
            'contact_id' => 'contact_id',
        ),
    ),
    'shop_referrals' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'code' => array('int', 11, 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'code',
            'contact_id' => 'contact_id',
        ),
    ),
);