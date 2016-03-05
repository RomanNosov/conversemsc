<?php

$model = new waModel();
try {
    $model->query('SELECT manager_id FROM shop_order WHERE 0');
    $model->exec("ALTER TABLE shop_order DROP manager_id");
} catch (waDbException $e) {
}
