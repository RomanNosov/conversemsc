<?php

return array(
    'name' => 'Менеджер заказа',
    'description' => '',
    'version'=>'1.1',
    'vendor' => 809114,
    'img' => 'img/logo.png',
    'handlers' => array(
        'backend_orders' => 'backendOrders',
        'backend_order' => 'backendOrder',
        'backend_reports' => 'backendReports',
        'order_action.create' => 'orderAction',
        'order_action.process' => 'orderAction',
        'order_action.pay' => 'orderAction',
        'order_action.ship' => 'orderAction',
        'order_action.refund' => 'orderAction',
        'order_action.edit' => 'orderAction',
        'order_action.delete' => 'orderAction',
        'order_action.restore' => 'orderAction',
        'order_action.complete' => 'orderAction',
        'order_action.comment' => 'orderAction',
        'order_action.message' => 'orderAction',
        'orders_collection' => 'ordersCollection',
    ),
    'shop_settings' => true,
);