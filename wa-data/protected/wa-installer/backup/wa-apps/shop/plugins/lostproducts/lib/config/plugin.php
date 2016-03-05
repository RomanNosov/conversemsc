<?php

return array(
    'name' => 'Lost products',
    'description' => 'Helps you find products with incomplete information.',
    'vendor' => 817747,
    'version' => '1.7.2',
    'img'=>'img/lostproducts16.png',
    'handlers' => array(
        'backend_products' => 'backendProducts',
        'products_collection' => 'productsCollection',
    ),
);
