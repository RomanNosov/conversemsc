<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
// Проверяем существование поля affiliate
try {
    $model->exec("SELECT affiliate FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD affiliate INT (11) NOT NULL DEFAULT '0'");
}

// Проверяем существование поля affiliate
try {
    $model->exec("SELECT affiliate FROM shop_flexdiscount_coupon_order WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount_coupon_order ADD affiliate INT (11) NOT NULL DEFAULT '0'");
}

try {
    $sql = <<<SQL
  CREATE TABLE IF NOT EXISTS `shop_flexdiscount_affiliate` (  
    `contact_id` int(11) NOT NULL,              
    `order_id` int(11) NOT NULL,                
    `affiliate` int(11) NOT NULL,         
    `status` int(1) NOT NULL DEFAULT '0'  
    PRIMARY KEY (`contact_id`,`order_id`)  
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
SQL;
    $model->exec($sql);
} catch (waDbException $e) {
}