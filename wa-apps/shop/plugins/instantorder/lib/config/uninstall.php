<?php

$model = new waModel();
try {
    $model->exec("DROP TABLE `shop_instantorder`");
} catch (waDbException $e) {
    
}


