<?php

$model = new waModel();
try {
    $model->query('SELECT manager_id FROM shop_order WHERE 0');
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_order ADD manager_id INT(11) NOT NULL DEFAULT 0");
    $model->exec("UPDATE shop_order JOIN (
          SELECT o.id order_id, l.id, l.contact_id
          FROM shop_order o
          JOIN shop_order_log l ON o.id = l.order_id
          WHERE l.contact_id != o.contact_id
          GROUP BY o.id
          HAVING l.id = MIN( l.id )
          ) AS t ON shop_order.id = t.order_id
          SET shop_order.manager_id = t.contact_id
          WHERE shop_order.manager_id = 0");
}
