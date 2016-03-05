<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    $model->exec("ALTER TABLE shop_flexdiscount CHANGE `value` `value` VARCHAR (30) NOT NULL");
} catch (waDbException $e) {
    
}
