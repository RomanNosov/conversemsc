<?php

return array(
    'name' => 'ЧПУ при импорте +',
    'description' => 'Создает ЧПУ при импорте новых товаров, меняет ссылки на существующие товары',
    'vendor' => '962376',
    'version' => '1.0.1',
    'img' => 'img/plugin.png',
    'shop_settings' => true,
    'handlers' => array (
        'product_save' => 'productSave',
    ),
);
//EOF