<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
$model = new waModel();
try {
    $model->exec("SELECT affiliate_percentage FROM shop_flexdiscount WHERE 0");
} catch (waDbException $e) {
    $model->exec("ALTER TABLE shop_flexdiscount ADD affiliate_percentage INT (3) NOT NULL DEFAULT '0'");
}
