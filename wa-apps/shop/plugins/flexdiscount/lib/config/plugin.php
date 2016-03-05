<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
return array(
    'name' => 'Гибкие скидки',
    'description' => 'Все виды скидок. Вывод цены со скидкой на Витрину. Отображение информации о скидках.',
    'img' => 'img/flexdiscount.png',
    'vendor' => '969712',
    'version' => '3.2.5',
    'frontend' => true,
    'handlers' => array(
        'order_calculate_discount' => 'orderCalculateDiscount',
        'backend_settings_discounts' => 'backendSettingsDiscounts',
        'frontend_cart'=> 'frontendCart',
        'frontend_head' => 'frontendHead',
        'frontend_product' => 'frontendProduct',
        'order_action.create' => 'orderActionCreate',
        'order_action.pay' => 'orderActionApplyAffiliate',
        'order_action.complete' => 'orderActionApplyAffiliate',
        'order_action.restore' => 'orderActionApplyAffiliate',
        'order_action.delete' => 'orderActionCancelAffiliate',
        'order_action.refund' => 'orderActionCancelAffiliate',
        'order_action.refund' => 'orderActionCancelAffiliate',
        'backend_order' => 'backendOrder',
    ),
);