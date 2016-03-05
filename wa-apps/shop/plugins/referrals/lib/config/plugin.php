<?php

return array(
    'name' => /*_wp*/('Referral program'),
    'description' => /*_wp*/('Credits affiliate bonuses for orders paid by referred customers and friends'),
    'img' => 'img/referrals.png',
    'shop_settings' => true,
    'version' => '1.1.0',
    'handlers' => array(
        'frontend_head' => 'frontendHead',
        'backend_settings_affiliate' => 'backendSettingsAffiliate',
        'backend_order' => 'backendOrder',
        'frontend_my_affiliate' => 'frontendMyAffiliate',
        'backend_customer' => 'backendCustomer',
        'backend_customers' => 'backendCustomers',
        'order_action.create' => 'orderCreate',
        'order_action.complete' => 'applyBonus',
        'order_action.pay' => 'applyBonus',
        'order_action.restore' => 'applyBonus',
        'order_action.delete' => 'cancelBonus',
        'order_action.refund' => 'cancelBonus',
        'reset' => 'reset',
    )
);