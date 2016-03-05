<?php

/**
 * @author  wa-plugins.ru <support@wa-plugins.ru>
 * @link http://wa-plugins.ru/
 */
return array(
    'name' => 'Быстрый заказ',
    'description' => 'Позволяет быстро оформить заказ',
    'vendor' => '985310',
    'version' => '1.2.0',
    'img' => 'img/instantorder.png',
    'frontend' => true,
    'shop_settings' => true,
    'handlers' => array(
        'frontend_product' => 'frontendProduct',
        'frontend_cart' => 'frontendCart',
    ),
);
//EOF
